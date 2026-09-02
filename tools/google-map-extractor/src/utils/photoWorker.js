import {
  getPendingPhotoQueue,
  updateQueueItemStatus,
  updatePlacePhotos,
  resetHangingQueueItems,
  getPhotoWorkerStats,
  enqueuePlacesForPhotoEnrichment,
  getTaskPlaces,
  getPlacesMissingPhotos,
  getActiveAccounts,
  incrementAccountUsage,
} from '../db/index.js';
import { scrapePlacePhotos } from './photoScraper.js';
import { logger } from './logger.js';

export { getPhotoWorkerStats };

let isRunning = false;
let loopTimeout = null;

/**
 * Initialize persistent photo worker (recovers any pending/hanging jobs from SQLite on startup)
 */
export function initPhotoWorker(broadcastCallback = null) {
  // Reset any tasks that were left in 'processing' state when server abruptly died/crashed
  resetHangingQueueItems();
  logger.info('[PersistentWorker] Recovered pending photo enrichment queue from database.');
  
  startWorkerLoop(broadcastCallback);
}

/**
 * Queue a task's places that have missing/low photos
 */
export function enqueueTaskForPhotoEnrichment(taskId, minPhotos = 3) {
  const places = getTaskPlaces(Number(taskId), 1000);
  const needIds = places
    .filter((p) => {
      const imgs = Array.isArray(p.imageUrls) ? p.imageUrls : [];
      return imgs.length < minPhotos;
    })
    .map((p) => p.id);

  if (needIds.length > 0) {
    enqueuePlacesForPhotoEnrichment(needIds);
    logger.info(`[PersistentWorker] Enqueued ${needIds.length} places from Task #${taskId} to persistent SQLite queue.`);
  }
  return needIds.length;
}

/**
 * Queue all places in the entire database that have 0 or 1 photo
 */
export function enqueueAllMissingPhotos(filter = 'low') {
  const { places } = getPlacesMissingPhotos({ filter, limit: 10000 });
  const ids = places.map((p) => p.id);
  if (ids.length > 0) {
    enqueuePlacesForPhotoEnrichment(ids);
    logger.info(`[PersistentWorker] Enqueued ${ids.length} places (0 or 1 photo) to persistent SQLite queue.`);
  }
  return ids.length;
}

/**
 * Queue a single specific place ID
 */
export function enqueueSinglePlace(placeId) {
  if (!placeId) return 0;
  return enqueuePlacesForPhotoEnrichment([Number(placeId)]);
}

const BATCH_DELAY_MS = 1500; // Polite delay between batches

/**
 * Helper to process a single queue item with a specific Apify token
 */
async function processQueueItem(item, token) {
  const placeQuery = `${item.title || ''} ${item.address || ''}`.trim();
  if (!placeQuery) {
    updateQueueItemStatus(item.queueId, 'completed');
    return { item, count: 0, success: true };
  }

  try {
    const photos = await scrapePlacePhotos(placeQuery, 6, {
      placeId: item.googlePlaceId,
      token,
    });
    if (photos && photos.length > 0) {
      updatePlacePhotos(item.placeId, photos);
      updateQueueItemStatus(item.queueId, 'completed');
      return { item, count: photos.length, success: true };
    } else {
      updateQueueItemStatus(item.queueId, 'completed');
      return { item, count: 0, success: true };
    }
  } catch (err) {
    updateQueueItemStatus(item.queueId, 'failed', err.message);
    return { item, count: 0, success: false, error: err.message };
  }
}

/**
 * Background loop that processes pending items in parallel.
 * - If 2 or more active Apify accounts exist: fetches 10 items (5 on API #1, 5 on API #2) concurrently.
 * - If 1 active account exists: fetches 5 items on 1 API concurrently.
 */
async function startWorkerLoop(broadcastCallback) {
  if (isRunning) return;
  isRunning = true;

  const onLog = (level, message, data = null) => {
    if (level === 'error') logger.error(message);
    else if (level === 'success') logger.success(message);
    else logger.info(message);

    if (typeof broadcastCallback === 'function') {
      try {
        broadcastCallback(level, message, data, 'worker');
      } catch {}
    }
  };

  const processBatch = async () => {
    try {
      const activeAccounts = getActiveAccounts();
      const hasMultipleAccounts = activeAccounts.length >= 2;
      const batchLimit = hasMultipleAccounts ? 10 : 5;

      const pendingItems = getPendingPhotoQueue(batchLimit);
      if (pendingItems.length === 0) {
        // Idle: check again in 3 seconds
        loopTimeout = setTimeout(processBatch, 3000);
        return;
      }

      // Mark all fetched items as 'processing' in SQLite
      for (const item of pendingItems) {
        updateQueueItemStatus(item.queueId, 'processing');
      }

      let tasks = [];

      if (hasMultipleAccounts) {
        const acc1 = activeAccounts[0];
        const acc2 = activeAccounts[1];
        incrementAccountUsage(acc1.id);
        incrementAccountUsage(acc2.id);

        const subBatch1 = pendingItems.slice(0, 5);
        const subBatch2 = pendingItems.slice(5);

        onLog(
          'info',
          `[PhotoWorker] 🚀 Processing ${pendingItems.length} places parallel across 2 Apify accounts (${acc1.email.split('@')[0]}: ${subBatch1.length}, ${acc2.email.split('@')[0]}: ${subBatch2.length})...`
        );

        tasks = [
          ...subBatch1.map((item) => processQueueItem(item, acc1.token)),
          ...subBatch2.map((item) => processQueueItem(item, acc2.token)),
        ];
      } else {
        const singleAcc = activeAccounts.length === 1 ? activeAccounts[0] : null;
        if (singleAcc) {
          incrementAccountUsage(singleAcc.id);
        }
        const token = singleAcc ? singleAcc.token : process.env.APIFY_TOKEN;

        onLog('info', `[PhotoWorker] 🚀 Processing batch of ${pendingItems.length} places with 1 Apify account...`);
        tasks = pendingItems.map((item) => processQueueItem(item, token));
      }

      const results = await Promise.allSettled(tasks);
      let successCount = 0;
      let totalPhotos = 0;

      for (const res of results) {
        if (res.status === 'fulfilled' && res.value?.count > 0) {
          successCount++;
          totalPhotos += res.value.count;
        }
      }

      onLog('success', `[PhotoWorker] ✔ Batch complete: Enriched ${totalPhotos} photos for ${successCount}/${pendingItems.length} places.`);

      // Delay before next batch
      await new Promise((r) => setTimeout(r, BATCH_DELAY_MS));
    } catch (loopErr) {
      onLog('error', `[PhotoWorker] Batch processing error: ${loopErr.message}`);
    }

    loopTimeout = setTimeout(processBatch, 500);
  };

  processBatch();
}
