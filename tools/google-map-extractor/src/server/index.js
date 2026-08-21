// HTTP API Server for v2 Web Dashboard — native Node.js http, zero framework deps.
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { parseArgs } from 'node:util';

import {
  getStats,
  getAccounts,
  saveAccount,
  updateAccount,
  deleteAccount,
  getTasks,
  getTaskById,
  getTaskPlaces,
  deleteTask,
  updateTask,
  getAllPlaces,
  getCategories,
  updatePlacePhotos,
  getPlacesMissingPhotos,
  getPhotoWorkerStats,
  getPhotoQueueList,
  clearPhotoQueue,
} from '../db/index.js';
import { scrapeGooglePlaces } from '../scraper.js';
import { createApifyAccount } from '../accountCreator.js';
import { createApifyAccountWithGithub, loadGithubAccounts } from '../accountCreatorGithub.js';
import { exportData } from '../exporters/index.js';
import { scrapePlacePhotos } from '../utils/photoScraper.js';
import {
  initPhotoWorker,
  enqueueTaskForPhotoEnrichment,
  enqueueAllMissingPhotos,
  enqueueSinglePlace,
} from '../utils/photoWorker.js';
import { readdir, stat as fsStat } from 'node:fs/promises';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(__dirname, '..', '..'); // v2/
const PUBLIC_DIR = path.join(ROOT, 'public');
const OUTPUT_DIR = path.join(ROOT, 'output');

// ─── SSE Log Broadcast ──────────────────────────────────────────
/** @type {Set<import('node:http').ServerResponse>} */
const sseClients = new Set();

/**
 * Broadcast log to SSE clients with channel support ('scraper', 'worker', 'all')
 */
export function broadcastLog(level, message, data = null, channel = 'scraper') {
  const payload = JSON.stringify({
    level,
    message,
    data,
    channel,
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
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
};

function json(res, body, status = 200) {
  res.writeHead(status, {
    'Content-Type': 'application/json; charset=utf-8',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
  });
  res.end(JSON.stringify(body));
}

async function readBody(req) {
  const chunks = [];
  for await (const c of req) chunks.push(c);
  const raw = Buffer.concat(chunks).toString('utf8');
  if (!raw) return {};
  try { return JSON.parse(raw); } catch { throw new Error('Invalid JSON body'); }
}

function matchRoute(method, url, targetMethod, pattern) {
  if (method !== targetMethod) return null;
  const [pathname] = url.split('?');
  const regex = new RegExp('^' + pattern.replace(/:(\w+)/g, '(?<$1>[^/]+)') + '$');
  const m = pathname.match(regex);
  return m ? (m.groups || {}) : null;
}

function queryParams(url) {
  const idx = url.indexOf('?');
  if (idx === -1) return {};
  const params = {};
  for (const [k, v] of new URLSearchParams(url.slice(idx + 1))) params[k] = v;
  return params;
}

// ─── Route Handlers ─────────────────────────────────────────────
async function handleApi(req, res) {
  const { method, url } = req;

  // CORS preflight
  if (method === 'OPTIONS') {
    res.writeHead(204, {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET,POST,PUT,PATCH,DELETE,OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
    });
    return res.end();
  }

  try {
    // GET /api/logs/stream — SSE
    if (method === 'GET' && url.startsWith('/api/logs/stream')) {
      res.writeHead(200, {
        'Content-Type': 'text/event-stream',
        'Cache-Control': 'no-cache',
        Connection: 'keep-alive',
        'Access-Control-Allow-Origin': '*',
      });
      res.write('\n');
      sseClients.add(res);
      req.on('close', () => sseClients.delete(res));
      return;
    }

    // GET /api/stats
    if (matchRoute(method, url, 'GET', '/api/stats')) {
      return json(res, getStats());
    }

    // GET /api/accounts
    if (matchRoute(method, url, 'GET', '/api/accounts')) {
      return json(res, getAccounts());
    }

    // POST /api/accounts/generate
    if (matchRoute(method, url, 'POST', '/api/accounts/generate')) {
      const body = await readBody(req);
      const methodType = body.method || 'github'; // default to github oauth

      if (methodType === 'github') {
        broadcastLog('info', 'Starting Apify account creation via GitHub OAuth...');
        try {
          const result = await createApifyAccountWithGithub({
            headless: body.headless ?? false,
            saveToEnv: false,
            onProgress: ({ level, message, data }) => broadcastLog(level, message, data),
          });
          broadcastLog('success', `GitHub account connected: ${result.email}`);
          return json(res, { success: true, account: result }, 201);
        } catch (err) {
          broadcastLog('error', `GitHub account creation failed: ${err.message}`);
          return json(res, { error: err.message }, 500);
        }
      } else {
        broadcastLog('info', 'Starting automated account creation via Email...');
        try {
          const result = await createApifyAccount({
            headless: body.headless ?? false,
            saveToEnv: false,
            onProgress: ({ level, message, data }) => broadcastLog(level, message, data),
          });
          broadcastLog('success', `Account created: ${result.email}`);
          return json(res, { success: true, account: result }, 201);
        } catch (err) {
          broadcastLog('error', `Account creation failed: ${err.message}`);
          return json(res, { error: err.message }, 500);
        }
      }
    }

    // GET /api/github-accounts
    if (matchRoute(method, url, 'GET', '/api/github-accounts')) {
      try {
        const list = await loadGithubAccounts();
        return json(res, list);
      } catch (e) {
        return json(res, []);
      }
    }

    // POST /api/accounts
    if (matchRoute(method, url, 'POST', '/api/accounts')) {
      const body = await readBody(req);
      const account = saveAccount(body);
      return json(res, account, 201);
    }

    // PATCH /api/accounts/:id/status
    let params;
    if ((params = matchRoute(method, url, 'PATCH', '/api/accounts/:id/status'))) {
      const body = await readBody(req);
      const account = updateAccount(Number(params.id), { status: body.status });
      return json(res, account);
    }

    // PUT /api/accounts/:id
    if ((params = matchRoute(method, url, 'PUT', '/api/accounts/:id'))) {
      const body = await readBody(req);
      const account = updateAccount(Number(params.id), body);
      return json(res, account);
    }

    // DELETE /api/accounts/:id
    if ((params = matchRoute(method, url, 'DELETE', '/api/accounts/:id'))) {
      const deleted = deleteAccount(Number(params.id));
      return json(res, { success: deleted }, deleted ? 200 : 404);
    }

    // GET /api/tasks
    if (matchRoute(method, url, 'GET', '/api/tasks')) {
      const q = queryParams(url);
      const limit = Number(q.limit) || 100;
      const offset = Number(q.offset) || 0;
      const tasks = getTasks(limit, offset);
      return json(res, tasks); // Return array directly for frontend convenience
    }

    // GET /api/tasks/:id/places
    if ((params = matchRoute(method, url, 'GET', '/api/tasks/:id/places'))) {
      const places = getTaskPlaces(Number(params.id));
      return json(res, places);
    }

    // GET /api/tasks/:id/download?format=xlsx
    if ((params = matchRoute(method, url, 'GET', '/api/tasks/:id/download'))) {
      const q = queryParams(url);
      const format = (q.format || 'xlsx').toLowerCase();
      const task = getTaskById(Number(params.id));
      if (!task) return json(res, { error: 'Task not found' }, 404);

      // Check if task already has export_files
      let targetFile = null;
      if (Array.isArray(task.exportFiles)) {
        const found = task.exportFiles.find(f => (f.format || '').toLowerCase() === format);
        if (found?.filePath) targetFile = found.filePath;
      }

      // If not pre-generated, generate on the fly
      if (!targetFile) {
        const places = getTaskPlaces(task.id);
        const exp = await exportData({
          items: places,
          format,
          outputDir: OUTPUT_DIR,
          querySlug: task.query || 'places',
        });
        if (exp.files?.length) targetFile = exp.files[0].filePath;
      }

      if (!targetFile) return json(res, { error: 'Could not generate download file' }, 500);

      const safe = path.basename(targetFile);
      const ext = path.extname(safe).toLowerCase();
      const mime = MIME[ext] || 'application/octet-stream';
      const fileStat = await fsStat(targetFile);

      res.writeHead(200, {
        'Content-Type': mime,
        'Content-Length': fileStat.size,
        'Content-Disposition': `attachment; filename="${safe}"`,
        'Access-Control-Allow-Origin': '*',
      });
      const { createReadStream } = await import('node:fs');
      createReadStream(targetFile).pipe(res);
      return;
    }

    // GET /api/tasks/:id
    if ((params = matchRoute(method, url, 'GET', '/api/tasks/:id'))) {
      const task = getTaskById(Number(params.id));
      if (!task) return json(res, { error: 'Task not found' }, 404);
      const places = getTaskPlaces(task.id);
      return json(res, { ...task, places });
    }

    // DELETE /api/tasks/:id
    if ((params = matchRoute(method, url, 'DELETE', '/api/tasks/:id'))) {
      const deleted = deleteTask(Number(params.id));
      return json(res, { success: deleted }, deleted ? 200 : 404);
    }

    // POST /api/tasks/:id/regenerate-export — Override/regenerate export files for a task
    if ((params = matchRoute(method, url, 'POST', '/api/tasks/:id/regenerate-export'))) {
      const taskId = Number(params.id);
      const task = getTaskById(taskId);
      if (!task) return json(res, { error: 'Task not found' }, 404);

      const body = await readBody(req);
      const format = (body.format || 'all').toLowerCase();
      const places = getTaskPlaces(taskId, 10000);

      if (!places.length) {
        return json(res, { error: 'No places found in this task to export' }, 400);
      }

      // Generate new files
      const exp = await exportData({
        items: places,
        format,
        outputDir: OUTPUT_DIR,
        querySlug: task.query || 'places',
      });

      // Update task record in SQLite
      updateTask(taskId, {
        exportFiles: exp.files,
        format,
      });

      broadcastLog('success', `[Export] Regenerated ${exp.files.length} export file(s) for Task #${taskId} (${format.toUpperCase()}).`);
      return json(res, { success: true, taskId, files: exp.files });
    }

    // GET /api/places — Search all scraped places across tasks
    if (matchRoute(method, url, 'GET', '/api/places')) {
      const q = queryParams(url);
      const result = getAllPlaces({
        search: q.search || '',
        category: q.category || '',
        taskId: q.taskId || null,
        photoFilter: q.photoFilter || 'all',
        limit: Number(q.limit) || 100,
        offset: Number(q.offset) || 0,
      });
      return json(res, result);
    }

    // GET /api/categories
    if (matchRoute(method, url, 'GET', '/api/categories')) {
      const categories = getCategories();
      return json(res, categories);
    }

    // GET /api/places/missing-photos — Get places with 0 or 1 photo
    if (matchRoute(method, url, 'GET', '/api/places/missing-photos')) {
      const q = queryParams(url);
      const filter = q.filter || 'low'; // 'zero' or 'low'
      const limit = Number(q.limit) || 100;
      const offset = Number(q.offset) || 0;
      const result = getPlacesMissingPhotos({ filter, limit, offset });
      return json(res, result);
    }

    // GET /api/worker/queue — List all photo worker jobs in SQLite queue with status filter
    if (matchRoute(method, url, 'GET', '/api/worker/queue')) {
      const q = queryParams(url);
      const status = q.status || 'all';
      const limit = Number(q.limit) || 100;
      const offset = Number(q.offset) || 0;
      const result = getPhotoQueueList({ status, limit, offset });
      return json(res, result);
    }

    // POST /api/worker/clear — Clear completed/failed worker queue items
    if (matchRoute(method, url, 'POST', '/api/worker/clear')) {
      const body = await readBody(req);
      const status = body.status || 'completed';
      const count = clearPhotoQueue(status);
      broadcastLog('info', `[Worker] Cleared ${count} ${status} items from queue.`, null, 'worker');
      return json(res, { success: true, count });
    }

    // GET /api/worker/stats — Get SQLite persistent photo worker queue stats
    if (matchRoute(method, url, 'GET', '/api/worker/stats')) {
      const stats = getPhotoWorkerStats();
      return json(res, stats);
    }

    // POST /api/worker/enqueue-all — Queue all 0/1 photo places to background worker
    if (matchRoute(method, url, 'POST', '/api/worker/enqueue-all')) {
      const body = await readBody(req);
      const filter = body.filter || 'low';
      const count = enqueueAllMissingPhotos(filter);
      broadcastLog('info', `[Worker] Enqueued ${count} places for background photo scraping.`, null, 'worker');
      return json(res, { success: true, count });
    }

    // POST /api/worker/enqueue-place — Queue a specific single place
    if (matchRoute(method, url, 'POST', '/api/worker/enqueue-place')) {
      const body = await readBody(req);
      const placeId = Number(body.placeId);
      const count = enqueueSinglePlace(placeId);
      broadcastLog('info', `[Worker] Enqueued place #${placeId} for photo scraping.`, null, 'worker');
      return json(res, { success: true, count });
    }

    // POST /api/worker/enqueue-task — Queue all places from a specific task
    if (matchRoute(method, url, 'POST', '/api/worker/enqueue-task')) {
      const body = await readBody(req);
      const taskId = Number(body.taskId);
      const count = enqueueTaskForPhotoEnrichment(taskId, body.minPhotos || 3);
      broadcastLog('info', `[Worker] Enqueued ${count} places from Task #${taskId} for photo scraping.`, null, 'worker');
      return json(res, { success: true, count });
    }

    // GET /api/places/photos — Search additional place photos on the fly via search engines and persist to DB
    if (matchRoute(method, url, 'GET', '/api/places/photos')) {
      const q = queryParams(url);
      const query = q.query || q.q || '';
      const placeId = q.placeId ? Number(q.placeId) : null;
      const googlePlaceId = q.googlePlaceId || null;
      const limit = Number(q.limit) || 12;
      try {
        const photos = await scrapePlacePhotos(query, limit, { placeId: googlePlaceId });
        if (placeId && photos.length > 0) {
          updatePlacePhotos(placeId, photos);
        }
        return json(res, { photos });
      } catch (err) {
        return json(res, { photos: [] });
      }
    }

    // GET /api/locations — Indonesian Provinces, Cities & Regencies for autocomplete/Select2
    if (matchRoute(method, url, 'GET', '/api/locations')) {
      const q = queryParams(url);
      const search = (q.search || q.q || '').toLowerCase().trim();
      const locationsFile = path.join(ROOT, 'src', 'data', 'locations.json');
      try {
        const raw = await readFile(locationsFile, 'utf8');
        let list = JSON.parse(raw);
        if (search) {
          list = list.filter((l) =>
            l.name.toLowerCase().includes(search) ||
            l.label.toLowerCase().includes(search) ||
            l.value.toLowerCase().includes(search) ||
            (l.province && l.province.toLowerCase().includes(search))
          );
        }
        return json(res, list.slice(0, 100));
      } catch (err) {
        return json(res, []);
      }
    }

    // GET /api/outputs — List all generated export files in output/
    if (matchRoute(method, url, 'GET', '/api/outputs')) {
      try {
        const fileNames = await readdir(OUTPUT_DIR);
        const files = [];
        for (const name of fileNames) {
          const fullPath = path.join(OUTPUT_DIR, name);
          try {
            const st = await fsStat(fullPath);
            if (st.isFile()) {
              const ext = path.extname(name).replace('.', '').toLowerCase();
              files.push({
                name,
                format: ext,
                size: st.size,
                createdAt: st.birthtime || st.mtime,
                downloadUrl: `/api/downloads/${encodeURIComponent(name)}`,
              });
            }
          } catch {}
        }
        files.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
        return json(res, files);
      } catch {
        return json(res, []);
      }
    }

    // POST /api/scrape
    if (matchRoute(method, url, 'POST', '/api/scrape')) {
      const body = await readBody(req);
      const startTime = Date.now();
      try {
        const result = await scrapeGooglePlaces({
          query: body.query,
          queries: body.queries || [body.query],
          location: body.location,
          maxPlaces: body.maxPlaces,
          format: body.format || 'none',
          accountId: body.accountId,
          rotateAccount: body.rotateAccount ?? true,
          language: body.language,
          countryCode: body.countryCode,
          includeReviews: body.includeReviews,
          includeImages: body.includeImages,
          outputDir: OUTPUT_DIR,
          onProgress: ({ level, message, data }) => broadcastLog(level, message, data, 'scraper'),
        });
        return json(res, {
          success: true,
          taskId: result.taskId,
          count: result.count,
          files: result.files,
          durationMs: result.durationMs ?? (Date.now() - startTime),
        });
      } catch (err) {
        return json(res, { error: err.message }, 500);
      }
    }

    // GET /api/downloads/:filename
    if ((params = matchRoute(method, url, 'GET', '/api/downloads/:filename'))) {
      const filename = decodeURIComponent(params.filename);
      // Prevent path traversal
      const safe = path.basename(filename);
      const filePath = path.join(OUTPUT_DIR, safe);
      if (!filePath.startsWith(OUTPUT_DIR)) {
        return json(res, { error: 'Forbidden' }, 403);
      }
      try {
        const info = await stat(filePath);
        if (!info.isFile()) return json(res, { error: 'Not found' }, 404);
        const ext = path.extname(safe).toLowerCase();
        const mime = MIME[ext] || 'application/octet-stream';
        res.writeHead(200, {
          'Content-Type': mime,
          'Content-Length': info.size,
          'Content-Disposition': `attachment; filename="${safe}"`,
          'Access-Control-Allow-Origin': '*',
        });
        const { createReadStream } = await import('node:fs');
        createReadStream(filePath).pipe(res);
      } catch {
        return json(res, { error: 'File not found' }, 404);
      }
      return;
    }

    // No matching API route
    return json(res, { error: 'Not found' }, 404);
  } catch (err) {
    return json(res, { error: err.message }, 400);
  }
}

async function serveStatic(req, res) {
  const [pathname] = req.url.split('?');
  let filePath = pathname === '/' ? path.join(PUBLIC_DIR, 'index.html') : path.join(PUBLIC_DIR, pathname);

  // Prevent path traversal
  if (!filePath.startsWith(PUBLIC_DIR)) {
    res.writeHead(403);
    return res.end('Forbidden');
  }

  try {
    const info = await stat(filePath);
    if (info.isDirectory()) filePath = path.join(filePath, 'index.html');
    const fileStat = await stat(filePath);
    if (!fileStat.isFile()) throw new Error('not file');

    const ext = path.extname(filePath).toLowerCase();
    const mime = MIME[ext] || 'application/octet-stream';
    const content = await readFile(filePath);

    res.writeHead(200, {
      'Content-Type': mime,
      'Content-Length': content.length,
      'Cache-Control': ext === '.html' ? 'no-cache' : 'public, max-age=3600',
    });
    res.end(content);
  } catch {
    // SPA fallback: serve index.html for non-file routes
    try {
      const fallback = path.join(PUBLIC_DIR, 'index.html');
      const content = await readFile(fallback);
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(content);
    } catch {
      res.writeHead(404);
      res.end('Not found');
    }
  }
}

// ─── Server ─────────────────────────────────────────────────────
export function startServer(port) {
  // Initialize persistent SQLite photo worker loop on startup
  initPhotoWorker(broadcastLog);

  const server = createServer(async (req, res) => {
    if (req.url?.startsWith('/api/')) {
      await handleApi(req, res);
    } else {
      await serveStatic(req, res);
    }
  });

  server.listen(port, () => {
    console.log(`[server] Dashboard running at http://localhost:${port}`);
    console.log(`[server] API available at http://localhost:${port}/api/`);
    console.log(`[server] SSE logs at http://localhost:${port}/api/logs/stream`);
  });

  return server;
}

// CLI entry point
const isMain = process.argv[1] &&
  (process.argv[1].endsWith('server/index.js') || process.argv[1].endsWith('server\\index.js'));

if (isMain) {
  let port = Number(process.env.PORT) || 3000;
  try {
    const { values } = parseArgs({ options: { port: { type: 'string', short: 'p' } }, strict: false });
    if (values.port) port = Number(values.port);
  } catch {}
  startServer(port);
}
