import {
  getPendingPhotoQueue,
  updateQueueItemStatus,
  updatePlacePhotos,
  resetHangingQueueItems,
  getPhotoWorkerStats,
  enqueuePlacesForPhotoEnrichment,
  getTaskPlaces,
  getPlacesMissingPhotos,
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

const BATCH_SIZE = 5; // Process 5 places in parallel concurrently (Apify free tier limit)
const BATCH_DELAY_MS = 1500; // Polite delay between batches

/**
 * Background loop that processes pending items in concurrent batches of 5
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
      // Fetch 10 places at once from SQLite queue
      const pendingItems = getPendingPhotoQueue(BATCH_SIZE);
      if (pendingItems.length === 0) {
        // Idle: check again in 3 seconds
        loopTimeout = setTimeout(processBatch, 3000);
        return;
      }

      // Mark all 10 items as 'processing' in SQLite
      for (const item of pendingItems) {
        updateQueueItemStatus(item.queueId, 'processing');
      }

      onLog('info', `[PhotoWorker] 🚀 Processing batch of ${pendingItems.length} places concurrently...`);

      // Execute all 10 place photo lookups in parallel via Promise.allSettled
      const tasks = pendingItems.map(async (item) => {
        const placeQuery = `${item.title || ''} ${item.address || ''}`.trim();
        if (!placeQuery) {
          updateQueueItemStatus(item.queueId, 'completed');
          return { item, count: 0 };
        }

        try {
          const photos = await scrapePlacePhotos(placeQuery, 6, {
            placeId: item.googlePlaceId,
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
      });

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

      // Delay before next concurrent batch
      await new Promise((r) => setTimeout(r, BATCH_DELAY_MS));
    } catch (loopErr) {
      onLog('error', `[PhotoWorker] Batch processing error: ${loopErr.message}`);
    }

    loopTimeout = setTimeout(processBatch, 500);
  };

  processBatch();
}
