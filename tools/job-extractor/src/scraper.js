import { ApifyClient } from 'apify-client';
import 'dotenv/config';
import fs from 'node:fs';
import path from 'node:path';
import { normalizeGoogleJob, normalizeInstagramPost, normalizeGlintsJob, normalizeJobstreetJob } from './normalizer.js';

export const ENGINE_GLINTS = 'truefetch/glints-job-listing';
export const ENGINE_JOBSTREET = 'shahidirfan/jobstreet-scraper';
export const ENGINE_GOOGLE_JOBS = 'spidersource/google-jobs-scraper';
export const ENGINE_INSTAGRAM = 'apify/instagram-post-scraper';

/**
 * Cari token Apify: customToken (parameter) atau APIFY_TOKEN dari .env lokal.
 */
export function resolveApifyToken(options = {}) {
  const { customToken } = typeof options === 'string' ? { customToken: options } : options;
  const token = customToken || process.env.APIFY_TOKEN || null;
  return { token };
}

export async function scrapeJobs(options = {}) {
  const {
    engine = ENGINE_JOBSTREET,
    query = 'lowongan barista bandung',
    location = '',
    maxItems = 20,
    token = null,
    outputDir = './output',
    onLog = null
  } = options;

  const log = (message, level = 'info') => {
    console.log(message);
    if (typeof onLog === 'function') {
      try {
        onLog(level, message);
      } catch {}
    }
  };

  const { token: resolvedToken } = resolveApifyToken({ customToken: token });
  if (!resolvedToken) {
    const errMsg = 'APIFY_TOKEN tidak ditemukan! Set APIFY_TOKEN di tools/job-extractor/.env.';
    log(`❌ ${errMsg}`, 'error');
    throw new Error(errMsg);
  }

  const client = new ApifyClient({ token: resolvedToken });

  let actorId = engine;
  let actorInput = {};

  if (engine === ENGINE_GLINTS) {
    actorId = ENGINE_GLINTS;
    const locClean = (location || '').trim();
    const cleanSearch = locClean ? `${query} ${locClean}` : query;
    actorInput = {
      keyword: cleanSearch,
      country: 'Indonesia',
      max_results: parseInt(maxItems, 10) || 20
    };
  } else if (engine === ENGINE_JOBSTREET) {
    actorId = ENGINE_JOBSTREET;
    actorInput = {
      keyword: query,
      location: (location || '').trim(),
      country: 'id',
      results_wanted: parseInt(maxItems, 10) || 20
    };
  } else if (engine === ENGINE_GOOGLE_JOBS) {
    // Gabungkan query dengan filter lokasi jika ada
    let queries = Array.isArray(query) ? query : [query];
    const locClean = (location || '').trim();
    if (locClean) {
      queries = queries.map(q => {
        const qTrim = q.trim();
        if (qTrim.toLowerCase().includes(locClean.toLowerCase())) return qTrim;
        return `${qTrim} in ${locClean}`;
      });
      log(`📍 Filter Lokasi Diterapkan: "${locClean}" -> Search Query: [${queries.join(', ')}]`);
    }

    actorInput = {
      queries,
      maxItems: parseInt(maxItems, 10) || 20,
      countryCode: 'id',
      languageCode: 'id'
    };
  } else if (engine === ENGINE_INSTAGRAM) {
    actorId = ENGINE_INSTAGRAM;
    const usernames = (Array.isArray(query) ? query : [query]).map(q => q.replace(/^@/, '').trim());
    actorInput = {
      username: usernames,
      resultsLimit: parseInt(maxItems, 10) || 20
    };
  } else {
    const errMsg = `Engine tidak dikenali: ${engine}`;
    log(`❌ ${errMsg}`, 'error');
    throw new Error(errMsg);
  }

  log(`🚀 Menjalankan actor: ${actorId}`);
  log(`🎯 Input parameters: ${JSON.stringify(actorInput)}`);

  const run = await client.actor(actorId).call(actorInput, {
    waitSecs: 300,
    memory: 2048
  });

  log(`✅ Run selesai (ID: ${run.id}). Mengambil dataset...`);

  const { items } = await client.dataset(run.defaultDatasetId).listItems();
  log(`📦 Diperoleh ${items.length} lowongan mentah dari Apify.`);

  const normalized = items.map(item => {
    if (engine === ENGINE_GLINTS) {
      return normalizeGlintsJob(item);
    } else if (engine === ENGINE_JOBSTREET) {
      return normalizeJobstreetJob(item);
    } else if (engine === ENGINE_GOOGLE_JOBS) {
      return normalizeGoogleJob(item);
    } else {
      return normalizeInstagramPost(item);
    }
  });

  // Buat folder output jika belum ada
  const resolvedOutDir = path.resolve(process.cwd(), outputDir);
  if (!fs.existsSync(resolvedOutDir)) {
    fs.mkdirSync(resolvedOutDir, { recursive: true });
  }

  const cleanQuery = (Array.isArray(query) ? query.join('_') : query).replace(/[^a-zA-Z0-9_-]/g, '_').toLowerCase();
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const fileName = `jobs_${cleanQuery}_${timestamp}.json`;
  const filePath = path.join(resolvedOutDir, fileName);

  fs.writeFileSync(filePath, JSON.stringify(normalized, null, 2), 'utf8');
  log(`💾 Data tersimpan rapi: ${filePath} (${normalized.length} lowongan)`, 'success');

  return {
    runId: run.id,
    count: normalized.length,
    fileName,
    filePath,
    data: normalized
  };
}
