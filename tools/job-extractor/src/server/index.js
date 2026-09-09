import { createServer } from 'node:http';
import { readFile, stat, readdir, unlink, writeFile } from 'node:fs/promises';
import { existsSync, mkdirSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { scrapeJobs, resolveApifyToken, getAvailableApifyAccounts, ENGINE_GOOGLE_JOBS, ENGINE_INSTAGRAM } from '../scraper.js';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..', '..');
const PUBLIC_DIR = path.join(ROOT, 'public');
const OUTPUT_DIR = path.join(ROOT, 'output');

if (!existsSync(OUTPUT_DIR)) {
  mkdirSync(OUTPUT_DIR, { recursive: true });
}

// ─── SSE Log Broadcast ──────────────────────────────────────────
/** @type {Set<import('node:http').ServerResponse>} */
const sseClients = new Set();

export function broadcastLog(level, message, data = null) {
  const payload = JSON.stringify({
    level,
    message,
    data,
    timestamp: new Date().toISOString(),
  });
  const chunk = `data: ${payload}\n\n`;
  for (const res of sseClients) {
    try {
      res.write(chunk);
    } catch {
      sseClients.delete(res);
    }
  }
}

// ─── Helpers ────────────────────────────────────────────────────
const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'application/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
};

function jsonResponse(res, status, data) {
  res.writeHead(status, {
    'Content-Type': 'application/json; charset=utf-8',
    'Access-Control-Allow-Origin': '*',
  });
  res.end(JSON.stringify(data));
}

function parseBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    req.on('data', chunk => chunks.push(chunk));
    req.on('end', () => {
      const raw = Buffer.concat(chunks).toString('utf8');
      if (!raw) return resolve({});
      try {
        resolve(JSON.parse(raw));
      } catch (err) {
        reject(new Error('Invalid JSON body'));
      }
    });
    req.on('error', reject);
  });
}

// ─── Output Files Helper ────────────────────────────────────────
async function getOutputFilesList() {
  if (!existsSync(OUTPUT_DIR)) return [];
  const files = await readdir(OUTPUT_DIR);
  const jsonFiles = files.filter(f => f.endsWith('.json'));

  const results = [];
  for (const f of jsonFiles) {
    const fullPath = path.join(OUTPUT_DIR, f);
    const st = await stat(fullPath);
    let count = 0;
    try {
      const content = await readFile(fullPath, 'utf8');
      const parsed = JSON.parse(content);
      count = Array.isArray(parsed) ? parsed.length : 0;
    } catch {}

    results.push({
      fileName: f,
      sizeBytes: st.size,
      itemCount: count,
      updatedAt: st.mtime,
      createdAt: st.birthtime,
    });
  }

  // sort newest first
  results.sort((a, b) => new Date(b.updatedAt) - new Date(a.updatedAt));
  return results;
}

// ─── Scrape Worker State ────────────────────────────────────────
let isScraping = false;

// ─── Server Request Router ──────────────────────────────────────
const server = createServer(async (req, res) => {
  // CORS Preflight
  if (req.method === 'OPTIONS') {
    res.writeHead(204, {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
    });
    res.end();
    return;
  }

  const parsedUrl = new URL(req.url, `http://${req.headers.host}`);
  const pathname = parsedUrl.pathname;

  // ─── SSE Log Stream ──────────────────────────────────────────
  if (pathname === '/api/events' && req.method === 'GET') {
    res.writeHead(200, {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection': 'keep-alive',
      'Access-Control-Allow-Origin': '*',
    });
    sseClients.add(res);
    res.write('data: {"type":"connected","message":"Connected to Job Extractor SSE"}\n\n');
    req.on('close', () => sseClients.delete(res));
    return;
  }

  // ─── API Routes ──────────────────────────────────────────────
  if (pathname.startsWith('/api/')) {
    try {
      // GET /api/stats
      if (pathname === '/api/stats' && req.method === 'GET') {
        const files = await getOutputFilesList();
        const totalJobs = files.reduce((acc, f) => acc + (f.itemCount || 0), 0);
        const { token } = resolveApifyToken();
        const availableAccounts = getAvailableApifyAccounts();

        return jsonResponse(res, 200, {
          totalFiles: files.length,
          totalJobs,
          isScraping,
          hasToken: !!token,
          maskedToken: token ? `${token.slice(0, 6)}...${token.slice(-4)}` : null,
          availableAccountsCount: availableAccounts.length,
          defaultEngine: ENGINE_GOOGLE_JOBS,
        });
      }

      // GET /api/accounts — Ambil akun Apify aktif dari SQLite google-map-extractor
      if (pathname === '/api/accounts' && req.method === 'GET') {
        const accounts = getAvailableApifyAccounts();
        return jsonResponse(res, 200, { accounts });
      }

      // GET /api/locations — Autocomplete kota / kabupaten / provinsi Indonesia
      if (pathname === '/api/locations' && req.method === 'GET') {
        const search = (parsedUrl.searchParams.get('q') || parsedUrl.searchParams.get('search') || '').toLowerCase().trim();
        const candidates = [
          path.resolve(ROOT, '..', 'google-map-extractor', 'src', 'data', 'locations.json'),
          path.resolve(ROOT, 'src', 'data', 'locations.json'),
          '/var/www/html/tools/google-map-extractor/src/data/locations.json'
        ];

        let list = [];
        for (const locFile of candidates) {
          if (existsSync(locFile)) {
            try {
              const raw = await readFile(locFile, 'utf8');
              list = JSON.parse(raw);
              break;
            } catch {}
          }
        }

        if (search && list.length > 0) {
          list = list.filter((l) =>
            (l.name && l.name.toLowerCase().includes(search)) ||
            (l.label && l.label.toLowerCase().includes(search)) ||
            (l.value && l.value.toLowerCase().includes(search)) ||
            (l.province && l.province.toLowerCase().includes(search))
          );
        }

        return jsonResponse(res, 200, list.slice(0, 100));
      }

      // GET /api/config
      if (pathname === '/api/config' && req.method === 'GET') {
        const { token } = resolveApifyToken();
        return jsonResponse(res, 200, {
          hasToken: !!token,
          maskedToken: token ? `${token.slice(0, 6)}...${token.slice(-4)}` : null,
          defaultEngine: ENGINE_GOOGLE_JOBS,
          engines: [
            { id: ENGINE_GOOGLE_JOBS, label: 'Google Jobs (spidersource/google-jobs-scraper)' },
            { id: ENGINE_INSTAGRAM, label: 'Instagram Loker (apify/instagram-post-scraper)' }
          ]
        });
      }

      // POST /api/config
      if (pathname === '/api/config' && req.method === 'POST') {
        const body = await parseBody(req);
        if (body.token) {
          const envPath = path.join(ROOT, '.env');
          await writeFile(envPath, `APIFY_TOKEN=${body.token.trim()}\n`, 'utf8');
          process.env.APIFY_TOKEN = body.token.trim();
          broadcastLog('info', 'Token Apify berhasil disimpan ke .env');
          return jsonResponse(res, 200, { success: true, message: 'Token berhasil disimpan' });
        }
        return jsonResponse(res, 400, { error: 'Token wajib diisi' });
      }

      // POST /api/scrape
      if (pathname === '/api/scrape' && req.method === 'POST') {
        if (isScraping) {
          return jsonResponse(res, 409, { error: 'Proses scraping lain sedang berjalan!' });
        }

        const body = await parseBody(req);
        const engine = body.engine || ENGINE_GOOGLE_JOBS;
        const query = body.query ? body.query.trim() : 'lowongan barista bandung';
        const location = body.location ? body.location.trim() : '';
        const maxItems = parseInt(body.maxItems, 10) || 20;
        const customToken = body.token || null;
        const accountMode = body.accountMode || 'auto';
        const accountId = accountMode === 'manual' && body.accountId ? Number(body.accountId) : null;
        const rotateAccount = accountMode === 'auto';

        isScraping = true;
        broadcastLog('info', `🚀 Memulai ekstraksi: Engine = ${engine}, Query = "${query}"${location ? `, Lokasi = "${location}"` : ''}, Limit = ${maxItems}, Mode Akun = ${accountMode}${accountId ? ` (#${accountId})` : ''}`);

        // Run scraper asynchronously
        (async () => {
          try {
            const result = await scrapeJobs({
              engine,
              query,
              location,
              maxItems,
              token: customToken,
              accountId,
              rotateAccount,
              outputDir: OUTPUT_DIR,
              onLog: (level, msg) => broadcastLog(level, msg)
            });

            broadcastLog('success', `🎉 Sukses! ${result.count} data lowongan berhasil diekstrak dan disimpan.`);
          } catch (err) {
            broadcastLog('error', `❌ Gagal: ${err.message}`);
          } finally {
            isScraping = false;
          }
        })();

        return jsonResponse(res, 202, {
          success: true,
          message: 'Scraping lowongan kerja dimulai di background',
          params: { engine, query, location, maxItems, accountMode, accountId }
        });
      }

      // GET /api/results
      if (pathname === '/api/results' && req.method === 'GET') {
        const files = await getOutputFilesList();
        return jsonResponse(res, 200, { files });
      }

      // GET /api/results/:filename
      const fileMatch = pathname.match(/^\/api\/results\/([^/]+)$/);
      if (fileMatch && req.method === 'GET') {
        const fileName = decodeURIComponent(fileMatch[1]);
        const fullPath = path.join(OUTPUT_DIR, fileName);
        if (!existsSync(fullPath)) {
          return jsonResponse(res, 404, { error: 'File tidak ditemukan' });
        }

        const content = await readFile(fullPath, 'utf8');
        try {
          const parsed = JSON.parse(content);
          return jsonResponse(res, 200, { fileName, data: parsed });
        } catch {
          return jsonResponse(res, 500, { error: 'Gagal mem-parsing file JSON' });
        }
      }

      // DELETE /api/results/:filename
      if (fileMatch && req.method === 'DELETE') {
        const fileName = decodeURIComponent(fileMatch[1]);
        const fullPath = path.join(OUTPUT_DIR, fileName);
        if (existsSync(fullPath)) {
          await unlink(fullPath);
          broadcastLog('info', `File ${fileName} berhasil dihapus.`);
          return jsonResponse(res, 200, { success: true });
        }
        return jsonResponse(res, 404, { error: 'File tidak ditemukan' });
      }

      return jsonResponse(res, 404, { error: 'Endpoint API tidak ditemukan' });
    } catch (err) {
      return jsonResponse(res, 500, { error: err.message });
    }
  }

  // ─── Static File Serving ──────────────────────────────────────
  let filePath = path.join(PUBLIC_DIR, pathname === '/' ? 'index.html' : pathname);

  try {
    const s = await stat(filePath);
    if (s.isDirectory()) {
      filePath = path.join(filePath, 'index.html');
    }
  } catch {
    // 404 fallback
    res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('404 Not Found');
    return;
  }

  const ext = path.extname(filePath).toLowerCase();
  const contentType = MIME[ext] || 'application/octet-stream';

  try {
    const content = await readFile(filePath);
    res.writeHead(200, { 'Content-Type': contentType });
    res.end(content);
  } catch (err) {
    res.writeHead(500, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('500 Internal Server Error');
  }
});

const PORT = parseInt(process.env.PORT, 10) || 3002;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`\n💼 Job Extractor Dashboard is running at: http://localhost:${PORT}`);
  console.log(`📡 Ready to stream scraper logs via SSE`);
});
