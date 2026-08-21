import { ApifyClient, ApifyApiError } from 'apify-client';
import 'dotenv/config';
import { logger } from './utils/logger.js';
import { exportData } from './exporters/index.js';
import { enqueueTaskForPhotoEnrichment } from './utils/photoWorker.js';
import {
  getNextRotatedAccount,
  getAccountById,
  incrementAccountUsage,
  setAccountStatus,
  createTask,
  updateTask,
  saveTaskPlaces,
} from './db/index.js';

export const DEFAULT_ACTOR = 'compass/crawler-google-places'; // Or nwua9Gu5YrADL7ZDj

/**
 * Scrape Google Maps places using Apify Actor with SQLite task tracking & account rotation
 *
 * @param {Object} options
 * @param {string} [options.token] - Specific Apify API Token (if omitted and rotateAccount is true, uses database rotation)
 * @param {number} [options.accountId] - Specific account ID from database to use
 * @param {boolean} [options.rotateAccount=false] - If true, auto-selects and rotates next active account from DB
 * @param {string|string[]} options.queries - Search query string or array of queries
 * @param {string} [options.location] - Location bounding string (e.g. 'Bandung, Indonesia')
 * @param {number} [options.maxPlaces=20] - Max places crawled per search query
 * @param {string} [options.language='id'] - Google Maps language code
 * @param {string} [options.countryCode='id'] - Country code
 * @param {boolean} [options.includeReviews=false] - Whether to scrape user reviews
 * @param {boolean} [options.includeImages=true] - Whether to scrape photo URLs
 * @param {boolean} [options.exportPlaceUrls=true] - Whether to export direct place URLs
 * @param {string} [options.format='all'] - Export format: 'all', 'xlsx', 'csv', 'json', 'xml'
 * @param {string} [options.outputDir='./output'] - Output directory for files
 * @param {string} [options.actorId] - Override actor ID / name
 * @param {number} [options.timeout=300] - Actor execution timeout in seconds
 * @param {number} [options.memory=4096] - Actor memory allocation in MB
 * @param {Function} [options.onProgress] - Callback for progress log messages: ({ level, message, data })
 * @returns {Promise<{taskId: number, runId: string, datasetId: string, items: Array, count: number, files: Array}>}
 */
export async function scrapeGooglePlaces(options = {}) {
  const startTime = Date.now();
  let token = options.token;
  let account = null;

  // Account resolution: Specific Account ID -> Database Rotation -> Provided Token -> Env Token
  if (options.accountId) {
    account = getAccountById(options.accountId);
    if (!account) {
      throw new Error(`Apify account with ID ${options.accountId} not found in database.`);
    }
    if (account.status !== 'active') {
      throw new Error(`Selected Apify account (${account.email}) is currently '${account.status}'.`);
    }
    token = account.token;
  } else if (options.rotateAccount || !token) {
    account = getNextRotatedAccount();
    if (account) {
      token = account.token;
      logger.info(`Auto-selected rotated account from DB: ${account.email} (Usage: ${account.usage_count})`);
    } else if (!token) {
      token = process.env.APIFY_TOKEN;
    }
  }

  if (!token) {
    throw new Error(
      'No Apify API Token found! Please add an account in the dashboard, run "npm run create-account", or set APIFY_TOKEN in .env.'
    );
  }

  const queries = Array.isArray(options.queries)
    ? options.queries
    : [options.queries || options.query || 'kafe di bandung'];

  const queryText = queries.join(', ');
  const location = options.location || '';
  const maxPlaces = Number(options.maxPlaces || options.maxCrawledPlacesPerSearch || 20);
  const language = options.language || 'id';
  const countryCode = options.countryCode || 'id';
  const includeReviews = Boolean(options.includeReviews);
  const includeImages = options.includeImages !== false;
  const exportPlaceUrls = options.exportPlaceUrls !== false;
  const format = options.format || 'all';
  const outputDir = options.outputDir || './output';
  const actorId = options.actorId || DEFAULT_ACTOR;
  const timeout = options.timeout || options.timeoutSecs || 300;
  const memory = options.memory || options.memoryMbytes || 4096;

  // Helper for reporting logs
  const report = (level, message, data = null) => {
    if (level === 'error') logger.error(message);
    else if (level === 'success') logger.success(message);
    else if (level === 'warn') logger.warn(message);
    else logger.info(message);

    if (typeof options.onProgress === 'function') {
      try {
        options.onProgress({ level, message, data, timestamp: new Date().toISOString() });
      } catch {}
    }
  };

  // 1. Create task entry in database
  const dbTask = createTask({
    query: queryText,
    location,
    maxPlaces,
    format,
    accountId: account?.id || null,
    accountEmail: account?.email || 'direct-token',
  });

  report('info', `Created Scrape Task #${dbTask.id} for "${queryText}"`);
  report('info', `Using Account: ${account ? `${account.email} (#${account.id})` : 'Direct Token'}`);
  report('info', `Target limit: ${maxPlaces} places | Language: ${language} | Format: ${format.toUpperCase()}`);

  updateTask(dbTask.id, { status: 'running' });

  const client = new ApifyClient({ token });

  const input = {
    searchStringsArray: queries,
    locationQuery: location,
    maxCrawledPlacesPerSearch: maxPlaces,
    language,
    countryCode,
    includeReviews,
    maxReviews: includeReviews ? 5 : 0,
    includeImages,
    maxImages: includeImages ? 20 : 0,
    exportPlaceUrls,
    scrapeCompanyContacts: false,
    scrapeLeads: false,
    proxyConfig: {
      useApifyProxy: true,
    },
  };

  try {
    report('info', `Calling Apify actor [${actorId}]...`);

    const run = await client.actor(actorId).call(input, {
      memory,
      timeout,
    });

    report('info', `Actor run ${run.id} finished with status: ${run.status}`);

    if (run.status !== 'SUCCEEDED') {
      throw new Error(`Actor run ${run.id} did not succeed (status: ${run.status})`);
    }

    // 2. Fetch dataset items
    report('info', `Retrieving scraped items from dataset ${run.defaultDatasetId}...`);
    const dataset = await client.dataset(run.defaultDatasetId).listItems({
      clean: true,
    });

    const items = dataset.items || [];
    report('success', `Fetched ${items.length} place records from Apify dataset!`);

    // 3. Save places to SQLite database
    const savedCount = saveTaskPlaces(dbTask.id, items);
    report('info', `Stored ${savedCount} normalized place records into database.`);

    // 4. Export to requested file formats (optional)
    let exportFiles = [];
    if (format && format !== 'none') {
      report('info', `Exporting data files (${format.toUpperCase()}) to ${outputDir}...`);
      const exportResult = await exportData({
        items,
        format,
        outputDir,
        querySlug: queries[0],
      });
      exportFiles = exportResult.files || [];
    } else {
      report('info', 'No export file format selected. Data saved exclusively to SQLite database.');
    }

    const durationMs = Date.now() - startTime;

    // 5. Update task record with completion
    updateTask(dbTask.id, {
      status: 'completed',
      items_count: items.length,
      run_id: run.id,
      dataset_id: run.defaultDatasetId,
      duration_ms: durationMs,
      export_files: exportFiles,
      completed_at: new Date().toISOString(),
    });

    if (account?.id) {
      incrementAccountUsage(account.id);
    }

    const exportMsg = exportFiles.length > 0 ? ` Generated ${exportFiles.length} file(s).` : ' (No files generated)';
    report('success', `Task #${dbTask.id} completed in ${(durationMs / 1000).toFixed(1)}s!${exportMsg}`);

    // 6. Trigger Persistent Background Photo Enrichment Worker (Non-blocking)
    try {
      enqueueTaskForPhotoEnrichment(dbTask.id, 3);
    } catch {}

    return {
      taskId: dbTask.id,
      runId: run.id,
      datasetId: run.defaultDatasetId,
      items,
      count: items.length,
      files: exportFiles,
      durationMs,
    };
  } catch (err) {
    const durationMs = Date.now() - startTime;
    const errorMsg = err.message || String(err);
    report('error', `Task #${dbTask.id} failed: ${errorMsg}`);

    // If unauthorized or rate limited, update account status in DB
    if (account?.id && err instanceof ApifyApiError) {
      if (err.statusCode === 401 || err.statusCode === 403) {
        setAccountStatus(account.id, 'invalid');
        report('warn', `Marked account #${account.id} (${account.email}) as 'invalid' due to auth error.`);
      } else if (err.statusCode === 429) {
        setAccountStatus(account.id, 'limited');
        report('warn', `Marked account #${account.id} (${account.email}) as 'limited' due to rate limit.`);
      }
    }

    updateTask(dbTask.id, {
      status: 'failed',
      duration_ms: durationMs,
      error_message: errorMsg,
      completed_at: new Date().toISOString(),
    });

    throw err;
  }
}

// Standalone CLI execution
if (process.argv[1] && process.argv[1].endsWith('scraper.js')) {
  const queryArg = process.argv.slice(2).join(' ') || 'kafe di bandung';
  logger.banner('Google Maps Extractor v2 (Apify Engine)');

  try {
    const result = await scrapeGooglePlaces({
      queries: [queryArg],
      maxPlaces: 10,
      rotateAccount: true,
    });

    logger.success(`Task #${result.taskId} finished with ${result.count} places!`);
  } catch (err) {
    logger.error(`Scraping failed: ${err.message}`);
    process.exit(1);
  }
}

