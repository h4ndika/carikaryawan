import { ApifyClient } from 'apify-client';
import 'dotenv/config';
import fs from 'node:fs';
import path from 'node:path';
import { normalizeGoogleJob, normalizeInstagramPost, normalizeGlintsJob, normalizeJobstreetJob } from './normalizer.js';

import { fileURLToPath } from 'node:url';
import { DatabaseSync } from 'node:sqlite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export const ENGINE_GLINTS = 'truefetch/glints-job-listing';
export const ENGINE_JOBSTREET = 'shahidirfan/jobstreet-scraper';
export const ENGINE_GOOGLE_JOBS = 'spidersource/google-jobs-scraper';
export const ENGINE_INSTAGRAM = 'apify/instagram-post-scraper';

/**
 * Mendapatkan koneksi SQLite ke database google-map-extractor
 */
export function getGmapsDatabase() {
  const candidates = [
    path.resolve(__dirname, '../../google-map-extractor/data/database.sqlite'),
    path.resolve(process.cwd(), '../google-map-extractor/data/database.sqlite'),
    '/var/www/html/tools/google-map-extractor/data/database.sqlite',
  ];

  for (const dbPath of candidates) {
    if (fs.existsSync(dbPath)) {
      try {
        return new DatabaseSync(dbPath);
      } catch (err) {
        console.warn(`[SQLite] Gagal membuka ${dbPath}: ${err.message}`);
      }
    }
  }
  return null;
}

/**
 * Ambil daftar akun Apify aktif dari google-map-extractor SQLite
 */
export function getAvailableApifyAccounts() {
  const db = getGmapsDatabase();
  if (!db) return [];
  try {
    const rows = db.prepare(`
      SELECT id, email, token, status, usage_count as usageCount, last_used_at as lastUsedAt 
      FROM apify_accounts 
      WHERE status = 'active' 
      ORDER BY usage_count ASC, id ASC
    `).all();
    return rows;
  } catch (err) {
    console.warn(`[SQLite] Error query apify_accounts: ${err.message}`);
    return [];
  }
}

/**
 * Cari token Apify: bisa dari akun spesifik (accountId), auto-rotate SQLite google-map-extractor,
 * atau fallback ke .env lokal / tools/google-map-extractor/.env
 */
export function resolveApifyToken(options = {}) {
  const { customToken, accountId, rotateAccount = false } = typeof options === 'string' ? { customToken: options } : options;
  if (customToken) return { token: customToken, account: null };

  const db = getGmapsDatabase();

  // 1. Manual Account ID
  if (db && accountId) {
    try {
      const row = db.prepare(`SELECT id, email, token, usage_count as usageCount FROM apify_accounts WHERE id = ?`).get(Number(accountId));
      if (row && row.token) {
        try {
          db.prepare(`UPDATE apify_accounts SET usage_count = usage_count + 1, last_used_at = datetime('now') WHERE id = ?`).run(row.id);
        } catch {}
        return { token: row.token, account: row };
      }
    } catch (err) {
      console.warn(`[SQLite] Gagal mengambil accountId ${accountId}: ${err.message}`);
    }
  }

  // 2. Auto-rotate dari akun aktif dengan usage paling rendah
  if (db && rotateAccount) {
    try {
      const row = db.prepare(`
        SELECT id, email, token, usage_count as usageCount 
        FROM apify_accounts 
        WHERE status = 'active' 
        ORDER BY usage_count ASC, last_used_at ASC, id ASC 
        LIMIT 1
      `).get();
      if (row && row.token) {
        try {
          db.prepare(`UPDATE apify_accounts SET usage_count = usage_count + 1, last_used_at = datetime('now') WHERE id = ?`).run(row.id);
        } catch {}
        return { token: row.token, account: row };
      }
    } catch (err) {
      console.warn(`[SQLite] Gagal auto-rotate akun: ${err.message}`);
    }
  }

  // 3. Fallback ke .env lokal
  if (process.env.APIFY_TOKEN) {
    return { token: process.env.APIFY_TOKEN, account: null };
  }

  // 4. Fallback ke .env google-map-extractor
  const gmapsEnvCandidates = [
    path.resolve(__dirname, '../../google-map-extractor/.env'),
    path.resolve(process.cwd(), '../google-map-extractor/.env'),
    '/var/www/html/tools/google-map-extractor/.env'
  ];
  for (const envPath of gmapsEnvCandidates) {
    if (fs.existsSync(envPath)) {
      const content = fs.readFileSync(envPath, 'utf8');
      const match = content.match(/APIFY_TOKEN=(.+)/);
      if (match && match[1]) {
        return { token: match[1].trim(), account: null };
      }
    }
  }

  return { token: null, account: null };
}

export async function scrapeJobs(options = {}) {
  const {
    engine = ENGINE_GOOGLE_JOBS,
    query = 'lowongan barista bandung',
    location = '',
    maxItems = 20,
    token = null,
    accountId = null,
    rotateAccount = true,
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

  const { token: resolvedToken, account } = resolveApifyToken({ customToken: token, accountId, rotateAccount });
  if (!resolvedToken) {
    const errMsg = 'APIFY_TOKEN tidak ditemukan! Pastikan akun Apify tersedia di google-map-extractor atau set di .env.';
    log(`❌ ${errMsg}`, 'error');
    throw new Error(errMsg);
  }

  if (account) {
    log(`🔑 Menggunakan Akun Apify: #${account.id} (${account.email || 'token'}) · Usage: ${account.usageCount || 0}x`);
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
