import { ApifyClient } from 'apify-client';
import 'dotenv/config';
import fs from 'node:fs';
import path from 'node:path';
import { normalizeGoogleJob, normalizeInstagramPost } from './normalizer.js';

export const ENGINE_GOOGLE_JOBS = 'spidersource/google-jobs-scraper';
export const ENGINE_INSTAGRAM = 'apify/instagram-post-scraper';

/**
 * Cari token Apify fallback dari .env lokal atau tools/google-map-extractor/.env
 */
export function resolveApifyToken(customToken) {
  if (customToken) return customToken;
  if (process.env.APIFY_TOKEN) return process.env.APIFY_TOKEN;

  const gmapsEnvPath = path.resolve(process.cwd(), '../google-map-extractor/.env');
  if (fs.existsSync(gmapsEnvPath)) {
    const content = fs.readFileSync(gmapsEnvPath, 'utf8');
    const match = content.match(/APIFY_TOKEN=(.+)/);
    if (match && match[1]) return match[1].trim();
  }

  return null;
}

export async function scrapeJobs(options = {}) {
  const {
    engine = ENGINE_GOOGLE_JOBS,
    query = 'lowongan barista bandung',
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

  const resolvedToken = resolveApifyToken(token);
  if (!resolvedToken) {
    const errMsg = 'APIFY_TOKEN tidak ditemukan! Set APIFY_TOKEN di .env atau sertakan flag --token.';
    log(`❌ ${errMsg}`, 'error');
    throw new Error(errMsg);
  }

  const client = new ApifyClient({ token: resolvedToken });

  let actorId = engine;
  let actorInput = {};

  if (engine === ENGINE_GOOGLE_JOBS) {
    actorInput = {
      queries: Array.isArray(query) ? query : [query],
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
    memoryMbytes: 2048
  });

  log(`✅ Run selesai (ID: ${run.id}). Mengambil dataset...`);

  const { items } = await client.dataset(run.defaultDatasetId).listItems();
  log(`📦 Diperoleh ${items.length} lowongan mentah dari Apify.`);

  const normalized = items.map(item => {
    if (engine === ENGINE_GOOGLE_JOBS) {
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
