// SQLite database layer for the Google Maps Extractor (v2).
// Uses the Node.js native `node:sqlite` (DatabaseSync) — no external dependencies.
// All row-returning helpers return camelCase objects (and JSON fields are parsed back).
import { DatabaseSync } from 'node:sqlite';
import { existsSync, mkdirSync, readFileSync, unlinkSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..'); // v2/
const DB_DIR = path.join(ROOT, 'data');
const DB_PATH = path.join(DB_DIR, 'database.sqlite');

mkdirSync(DB_DIR, { recursive: true });

let db;
try {
  db = new DatabaseSync(DB_PATH, { enableForeignKeyConstraints: true });
} catch (err) {
  throw new Error(`Failed to open SQLite database at ${DB_PATH}: ${err.message}`);
}
db.exec('PRAGMA journal_mode = WAL;');
db.exec('PRAGMA busy_timeout = 5000;');

db.exec(`
  CREATE TABLE IF NOT EXISTS apify_accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password TEXT,
    token TEXT NOT NULL UNIQUE,
    user_id TEXT,
    username TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active','limited','invalid','disabled')),
    usage_count INTEGER DEFAULT 0,
    last_used_at TEXT,
    created_at TEXT DEFAULT (datetime('now'))
  );

  CREATE TABLE IF NOT EXISTS scrape_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    query TEXT NOT NULL,
    location TEXT,
    max_places INTEGER DEFAULT 20,
    format TEXT DEFAULT 'all',
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending','running','completed','failed','stopped')),
    items_count INTEGER DEFAULT 0,
    account_id INTEGER REFERENCES apify_accounts(id),
    account_email TEXT,
    run_id TEXT,
    dataset_id TEXT,
    duration_ms INTEGER DEFAULT 0,
    error_message TEXT,
    export_files TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    completed_at TEXT
  );

  CREATE TABLE IF NOT EXISTS scraped_places (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    task_id INTEGER NOT NULL REFERENCES scrape_tasks(id),
    title TEXT,
    category TEXT,
    rating REAL,
    reviews_count INTEGER,
    phone TEXT,
    website TEXT,
    address TEXT,
    latitude REAL,
    longitude REAL,
    price TEXT,
    opening_hours TEXT,
    description TEXT,
    facilities TEXT,
    google_maps_url TEXT,
    image_urls TEXT,
    place_id TEXT,
    cid TEXT,
    raw_json TEXT,
    created_at TEXT DEFAULT (datetime('now'))
  );

  CREATE TABLE IF NOT EXISTS photo_enrichment_queue (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    place_id INTEGER NOT NULL UNIQUE REFERENCES scraped_places(id),
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending','processing','completed','failed')),
    attempts INTEGER DEFAULT 0,
    error_message TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
  );

  CREATE INDEX IF NOT EXISTS idx_photo_queue_status ON photo_enrichment_queue(status);
  CREATE INDEX IF NOT EXISTS idx_scraped_places_task_id ON scraped_places(task_id);
  CREATE INDEX IF NOT EXISTS idx_scrape_tasks_created_at ON scrape_tasks(created_at DESC);
  CREATE INDEX IF NOT EXISTS idx_apify_accounts_status ON apify_accounts(status);
`);

// ─── Small helpers ──────────────────────────────────────────────
const toCamel = (row) => {
  const out = {};
  for (const [key, value] of Object.entries(row)) {
    out[key.replace(/_([a-z])/g, (_, c) => c.toUpperCase())] = value;
  }
  return out;
};

const parseJson = (str) => {
  if (str == null || str === '') return null;
  try {
    return JSON.parse(str);
  } catch {
    return str;
  }
};

const toNum = (v) => {
  if (v === undefined || v === null || v === '') return null;
  const n = Number(v);
  return Number.isFinite(n) ? n : null;
};

const pick = (item, keys) => {
  for (const key of keys) {
    const v = item[key];
    if (v !== undefined && v !== null && v !== '') return v;
  }
  return null;
};

// ─── Accounts ───────────────────────────────────────────────────
const ACCOUNT_STATUSES = new Set(['active', 'limited', 'invalid', 'disabled']);
const ACCOUNT_COLUMNS = {
  email: 'email',
  password: 'password',
  token: 'token',
  userId: 'user_id',
  username: 'username',
  status: 'status',
  usageCount: 'usage_count',
  lastUsedAt: 'last_used_at',
};

const accountFromId = (id) => {
  const row = db.prepare('SELECT * FROM apify_accounts WHERE id = ?').get(id);
  return row ? toCamel(row) : null;
};

/** @returns {Promise<Array>} all accounts, newest first */
export function getAccounts() {
  return db
    .prepare('SELECT * FROM apify_accounts ORDER BY created_at DESC, id DESC')
    .all()
    .map(toCamel);
}

export function getAccountById(id) {
  if (!Number.isInteger(Number(id))) throw new TypeError('getAccountById: id must be an integer');
  return accountFromId(Number(id));
}

export function getAccountByToken(token) {
  if (typeof token !== 'string' || !token) throw new TypeError('getAccountByToken: token must be a non-empty string');
  const row = db.prepare('SELECT * FROM apify_accounts WHERE token = ?').get(token);
  return row ? toCamel(row) : null;
}

export function getAccountByEmail(email) {
  if (typeof email !== 'string') throw new TypeError('getAccountByEmail: email must be a string');
  const row = db.prepare('SELECT * FROM apify_accounts WHERE email = ?').get(email);
  return row ? toCamel(row) : null;
}

/** Insert a new account, or update the existing one matching token/email. */
export function saveAccount({ email, password, token, userId, username, status } = {}) {
  if (typeof token !== 'string' || !token) throw new TypeError('saveAccount: token is required');
  const cleanEmail = typeof email === 'string' && email ? email : '';
  const existing = getAccountByToken(token);
  if (existing) {
    // Only pass fields that are explicitly provided (non-undefined).
    const upd = {};
    if (email !== undefined) upd.email = cleanEmail;
    if (password !== undefined) upd.password = password;
    if (userId !== undefined) upd.userId = userId;
    if (username !== undefined) upd.username = username;
    if (status !== undefined) upd.status = status;
    return updateAccount(existing.id, upd);
  }
  const byEmail = cleanEmail ? getAccountByEmail(cleanEmail) : null;
  if (byEmail) {
    const upd = { token };
    if (password !== undefined) upd.password = password;
    if (userId !== undefined) upd.userId = userId;
    if (username !== undefined) upd.username = username;
    if (status !== undefined) upd.status = status;
    return updateAccount(byEmail.id, upd);
  }
  const res = db
    .prepare(
      `INSERT INTO apify_accounts (email, password, token, user_id, username, status)
       VALUES (?, ?, ?, ?, ?, ?)`
    )
    .run(cleanEmail, password ?? null, token, userId ?? null, username ?? null, status || 'active');
  return accountFromId(Number(res.lastInsertRowid));
}

/** Update allowed account fields; returns the updated account (or the current one if nothing changed). */
export function updateAccount(id, fields = {}) {
  if (!Number.isInteger(Number(id))) throw new TypeError('updateAccount: id must be an integer');
  if (!fields || typeof fields !== 'object') throw new TypeError('updateAccount: fields must be an object');
  const sets = [];
  const values = [];
  for (const [key, column] of Object.entries(ACCOUNT_COLUMNS)) {
    if (fields[key] === undefined) continue;
    if (key === 'status' && !ACCOUNT_STATUSES.has(fields[key])) {
      throw new TypeError(`updateAccount: invalid status "${fields[key]}"`);
    }
    sets.push(`${column} = ?`);
    values.push(fields[key]);
  }
  if (sets.length > 0) {
    values.push(Number(id));
    db.prepare(`UPDATE apify_accounts SET ${sets.join(', ')} WHERE id = ?`).run(...values);
  }
  return accountFromId(Number(id));
}

export function deleteAccount(id) {
  if (!Number.isInteger(Number(id))) throw new TypeError('deleteAccount: id must be an integer');
  return db.prepare('DELETE FROM apify_accounts WHERE id = ?').run(Number(id)).changes > 0;
}

export function getActiveAccounts(limit = null) {
  let query = `
    SELECT * FROM apify_accounts
    WHERE status = 'active'
    ORDER BY usage_count ASC, (last_used_at IS NULL) DESC, last_used_at ASC
  `;
  if (limit && Number.isInteger(Number(limit))) {
    query += ` LIMIT ${Number(limit)}`;
  }
  return db.prepare(query).all().map(toCamel);
}

/**
 * Pick the next active account for scraping: lowest usage_count, then oldest
 * last_used_at (never-used accounts first). Increments its usage_count and
 * refreshes last_used_at. Returns the updated account, or null if none active.
 */
export function getNextRotatedAccount() {
  const row = db
    .prepare(
      `SELECT * FROM apify_accounts
       WHERE status = 'active'
       ORDER BY usage_count ASC, (last_used_at IS NULL) DESC, last_used_at ASC
       LIMIT 1`
    )
    .get();
  if (!row) return null;
  return incrementAccountUsage(Number(row.id));
}

export function incrementAccountUsage(id) {
  if (!Number.isInteger(Number(id))) throw new TypeError('incrementAccountUsage: id must be an integer');
  db.prepare(
    "UPDATE apify_accounts SET usage_count = usage_count + 1, last_used_at = datetime('now') WHERE id = ?"
  ).run(Number(id));
  return accountFromId(Number(id));
}

export function setAccountStatus(id, status) {
  if (!ACCOUNT_STATUSES.has(status)) throw new TypeError(`setAccountStatus: invalid status "${status}"`);
  return updateAccount(id, { status });
}

// ─── Tasks / Scrape history ─────────────────────────────────────
const TASK_STATUSES = new Set(['pending', 'running', 'completed', 'failed', 'stopped']);
const FINAL_STATUSES = new Set(['completed', 'failed', 'stopped']);
const TASK_COLUMNS = {
  status: 'status',
  itemsCount: 'items_count',
  durationMs: 'duration_ms',
  errorMessage: 'error_message',
  exportFiles: 'export_files',
  completedAt: 'completed_at',
  runId: 'run_id',
  datasetId: 'dataset_id',
  accountId: 'account_id',
  accountEmail: 'account_email',
  query: 'query',
  location: 'location',
  maxPlaces: 'max_places',
  format: 'format',
};

const taskFromId = (id) => {
  const row = db.prepare('SELECT * FROM scrape_tasks WHERE id = ?').get(id);
  return row ? toTask(row) : null;
};

const toTask = (row) => {
  const task = toCamel(row);
  task.exportFiles = parseJson(task.exportFiles);
  return task;
};

export function createTask({ query, location, maxPlaces, format, accountId, accountEmail } = {}) {
  if (typeof query !== 'string' || !query.trim()) throw new TypeError('createTask: query is required');
  const res = db
    .prepare(
      `INSERT INTO scrape_tasks (query, location, max_places, format, account_id, account_email)
       VALUES (?, ?, ?, ?, ?, ?)`
    )
    .run(
      query.trim(),
      location ?? null,
      toNum(maxPlaces) ?? 20,
      format || 'all',
      accountId ?? null,
      accountEmail ?? null
    );
  return taskFromId(Number(res.lastInsertRowid));
}

/** Update allowed task fields; exportFiles is JSON-stringified automatically. Returns the updated task. */
export function updateTask(id, fields = {}) {
  if (!Number.isInteger(Number(id))) throw new TypeError('updateTask: id must be an integer');
  if (!fields || typeof fields !== 'object') throw new TypeError('updateTask: fields must be an object');
  const sets = [];
  const values = [];
  for (const [key, column] of Object.entries(TASK_COLUMNS)) {
    if (fields[key] === undefined) continue;
    let value = fields[key];
    if (key === 'status' && !TASK_STATUSES.has(value)) {
      throw new TypeError(`updateTask: invalid status "${value}"`);
    }
    if (key === 'exportFiles' && value !== null && typeof value === 'object') {
      value = JSON.stringify(value);
    }
    sets.push(`${column} = ?`);
    values.push(value);
  }
  // Auto-stamp completed_at when a task reaches a final status and none was given.
  if (fields.status && FINAL_STATUSES.has(fields.status) && fields.completedAt === undefined) {
    sets.push("completed_at = datetime('now')");
  }
  if (sets.length > 0) {
    values.push(Number(id));
    db.prepare(`UPDATE scrape_tasks SET ${sets.join(', ')} WHERE id = ?`).run(...values);
  }
  return taskFromId(Number(id));
}

/** @returns {Array} tasks with placeCount, newest first */
export function getTasks(limit = 50, offset = 0) {
  if (!Number.isInteger(Number(limit))) throw new TypeError('getTasks: limit must be an integer');
  if (!Number.isInteger(Number(offset))) throw new TypeError('getTasks: offset must be an integer');
  return db
    .prepare(
      `SELECT t.*, COALESCE(pc.place_count, 0) AS place_count
       FROM scrape_tasks t
       LEFT JOIN (SELECT task_id, COUNT(*) AS place_count FROM scraped_places GROUP BY task_id) pc
         ON pc.task_id = t.id
       ORDER BY t.created_at DESC, t.id DESC
       LIMIT ? OFFSET ?`
    )
    .all(Number(limit), Number(offset))
    .map(toTask);
}

export function getTaskById(id) {
  if (!Number.isInteger(Number(id))) throw new TypeError('getTaskById: id must be an integer');
  return taskFromId(Number(id));
}

/** Delete a task, its photo enrichment queue items, scraped places, and task record. */
export function deleteTask(id) {
  if (!Number.isInteger(Number(id))) throw new TypeError('deleteTask: id must be an integer');
  const taskId = Number(id);
  const task = getTaskById(taskId);

  db.exec('BEGIN');
  try {
    // 1. Delete from photo_enrichment_queue for all places belonging to this task
    db.prepare(`
      DELETE FROM photo_enrichment_queue 
      WHERE place_id IN (SELECT id FROM scraped_places WHERE task_id = ?)
    `).run(taskId);

    // 2. Delete all scraped places belonging to this task
    db.prepare('DELETE FROM scraped_places WHERE task_id = ?').run(taskId);

    // 3. Delete the scrape task record
    const res = db.prepare('DELETE FROM scrape_tasks WHERE id = ?').run(taskId);

    db.exec('COMMIT');

    // 4. Clean up any physical export files associated with the task
    if (task && Array.isArray(task.exportFiles)) {
      for (const filePath of task.exportFiles) {
        try {
          if (filePath && existsSync(filePath)) {
            unlinkSync(filePath);
          }
        } catch {}
      }
    }

    return res.changes > 0;
  } catch (err) {
    db.exec('ROLLBACK');
    throw err;
  }
}

// ─── Places ─────────────────────────────────────────────────────
const PLACE_COLUMNS = [
  'task_id', 'title', 'category', 'rating', 'reviews_count', 'phone', 'website',
  'address', 'latitude', 'longitude', 'price', 'opening_hours', 'description',
  'facilities', 'google_maps_url', 'image_urls', 'place_id', 'cid', 'raw_json',
];

const toStr = (v) => {
  if (v === undefined || v === null) return '';
  if (typeof v === 'string') return v;
  if (Array.isArray(v)) {
    return v.map((item) => (typeof item === 'object' && item !== null ? JSON.stringify(item) : String(item))).join('; ');
  }
  if (typeof v === 'object') return JSON.stringify(v);
  return String(v);
};

const normalizePlaceRow = (taskId, item) => {
  let imageUrls = pick(item, ['imageUrls', 'image_urls', 'imageUrl', 'images', 'photos']);
  if (typeof imageUrls === 'string') {
    if (imageUrls.startsWith('http')) imageUrls = [imageUrls];
    else {
      try { imageUrls = JSON.parse(imageUrls); } catch { imageUrls = [imageUrls]; }
    }
  } else if (imageUrls && !Array.isArray(imageUrls) && typeof imageUrls === 'object') {
    imageUrls = [imageUrls.url || imageUrls.img || JSON.stringify(imageUrls)];
  }

  const rawJson = pick(item, ['rawJson', 'raw_json']);
  return [
    taskId,
    toStr(pick(item, ['title', 'name'])),
    toStr(pick(item, ['category', 'categoryName'])),
    toNum(pick(item, ['rating', 'totalScore'])),
    toNum(pick(item, ['reviewsCount', 'reviews_count'])),
    toStr(pick(item, ['phone', 'phoneUnformatted'])),
    toStr(pick(item, ['website', 'url'])),
    toStr(pick(item, ['address'])),
    toNum(pick(item, ['latitude', 'lat']) ?? item.location?.lat),
    toNum(pick(item, ['longitude', 'lng']) ?? item.location?.lng),
    toStr(pick(item, ['price', 'priceLevel'])),
    toStr(pick(item, ['openingHours', 'opening_hours'])),
    toStr(pick(item, ['description', 'about'])),
    toStr(pick(item, ['facilities', 'amenities'])),
    toStr(pick(item, ['googleMapsUrl', 'google_maps_url', 'searchPageUrl'])),
    imageUrls && Array.isArray(imageUrls) && imageUrls.length ? JSON.stringify(imageUrls) : null,
    toStr(pick(item, ['placeId', 'place_id'])),
    toStr(pick(item, ['cid'])),
    rawJson !== null ? (typeof rawJson === 'string' ? rawJson : JSON.stringify(rawJson)) : null,
  ];
};

/** Batch-insert normalized places (camelCase or snake_case keys accepted). Returns inserted count. */
export function saveTaskPlaces(taskId, items = []) {
  if (!Number.isInteger(Number(taskId))) throw new TypeError('saveTaskPlaces: taskId must be an integer');
  if (!Array.isArray(items)) throw new TypeError('saveTaskPlaces: items must be an array');
  const valid = items.filter((item) => item && typeof item === 'object');
  if (valid.length === 0) return 0;

  const stmt = db.prepare(
    `INSERT INTO scraped_places (${PLACE_COLUMNS.join(', ')})
     VALUES (${PLACE_COLUMNS.map(() => '?').join(', ')})`
  );
  db.exec('BEGIN');
  try {
    for (const item of valid) stmt.run(...normalizePlaceRow(Number(taskId), item));
    db.exec('COMMIT');
  } catch (err) {
    db.exec('ROLLBACK');
    throw err;
  }
  return valid.length;
}

export function getTaskPlaces(taskId, limit = 500) {
  if (!Number.isInteger(Number(taskId))) throw new TypeError('getTaskPlaces: taskId must be an integer');
  if (!Number.isInteger(Number(limit))) throw new TypeError('getTaskPlaces: limit must be an integer');
  return db
    .prepare('SELECT * FROM scraped_places WHERE task_id = ? ORDER BY id ASC LIMIT ?')
    .all(Number(taskId), Number(limit))
    .map((row) => {
      const place = toCamel(row);
      place.imageUrls = parseJson(place.imageUrls);
      return place;
    });
}

/** Update place photos / imageUrls in database */
export function updatePlacePhotos(placeId, imageUrls = []) {
  if (!Number.isInteger(Number(placeId))) return false;
  if (!Array.isArray(imageUrls) || !imageUrls.length) return false;

  const row = db.prepare('SELECT image_urls FROM scraped_places WHERE id = ?').get(Number(placeId));
  if (!row) return false;

  let existing = parseJson(row.image_urls) || [];
  if (!Array.isArray(existing)) existing = [];

  const merged = Array.from(new Set([...existing, ...imageUrls])).filter(
    (url) => typeof url === 'string' && url.startsWith('http')
  );

  db.prepare('UPDATE scraped_places SET image_urls = ? WHERE id = ?').run(
    JSON.stringify(merged),
    Number(placeId)
  );

  return true;
}

/** Get single place by ID */
export function getPlaceById(id) {
  if (!Number.isInteger(Number(id))) return null;
  const row = db.prepare('SELECT p.*, t.query AS task_query FROM scraped_places p LEFT JOIN scrape_tasks t ON t.id = p.task_id WHERE p.id = ?').get(Number(id));
  if (!row) return null;
  const place = toCamel(row);
  place.imageUrls = parseJson(place.imageUrls);
  return place;
}

/** Get places missing photos or with only 1 photo (photoFilter: 'missing' = 0 photos, 'low' = <=1 photo, 'all') */
export function getPlacesMissingPhotos({ filter = 'low', limit = 100, offset = 0 } = {}) {
  const query = `
    SELECT p.*, t.query AS task_query
    FROM scraped_places p
    LEFT JOIN scrape_tasks t ON t.id = p.task_id
    ORDER BY p.id DESC
  `;
  const allRows = db.prepare(query).all().map(r => {
    const p = toCamel(r);
    p.imageUrls = parseJson(p.imageUrls);
    return p;
  });

  const filtered = allRows.filter(p => {
    const imgList = Array.isArray(p.imageUrls) ? p.imageUrls : [];
    if (filter === 'zero') return imgList.length === 0;
    return imgList.length <= 1; // default 'low'
  });

  const total = filtered.length;
  const places = filtered.slice(Number(offset), Number(offset) + Number(limit));
  return { places, total, limit: Number(limit), offset: Number(offset) };
}

// ─── Photo Enrichment Queue in SQLite (Persistent Worker) ────────
export function enqueuePlacesForPhotoEnrichment(placeIds = []) {
  if (!Array.isArray(placeIds) || !placeIds.length) return 0;
  const stmt = db.prepare(`
    INSERT INTO photo_enrichment_queue (place_id, status)
    VALUES (?, 'pending')
    ON CONFLICT(place_id) DO UPDATE SET status = 'pending', attempts = 0, updated_at = datetime('now')
  `);
  db.exec('BEGIN');
  let count = 0;
  try {
    for (const pid of placeIds) {
      if (Number.isInteger(Number(pid))) {
        stmt.run(Number(pid));
        count++;
      }
    }
    db.exec('COMMIT');
  } catch (err) {
    db.exec('ROLLBACK');
    throw err;
  }
  return count;
}

export function getPendingPhotoQueue(limit = 50) {
  const query = `
    SELECT q.id AS queue_id, q.place_id, q.status, q.attempts,
           p.title, p.address, p.place_id AS google_place_id, p.image_urls
    FROM photo_enrichment_queue q
    JOIN scraped_places p ON p.id = q.place_id
    WHERE q.status = 'pending' AND q.attempts < 3
    ORDER BY q.id ASC
    LIMIT ?
  `;
  return db.prepare(query).all(Number(limit)).map(r => {
    const item = toCamel(r);
    item.imageUrls = parseJson(item.imageUrls);
    return item;
  });
}

export function updateQueueItemStatus(queueId, status, errorMessage = null) {
  db.prepare(`
    UPDATE photo_enrichment_queue
    SET status = ?, error_message = ?, attempts = attempts + 1, updated_at = datetime('now')
    WHERE id = ?
  `).run(status, errorMessage, Number(queueId));
}

export function resetHangingQueueItems() {
  db.prepare(`
    UPDATE photo_enrichment_queue
    SET status = 'pending'
    WHERE status = 'processing'
  `).run();
}

/** Get full photo enrichment queue list with pagination & status filter */
export function getPhotoQueueList({ status = 'all', limit = 100, offset = 0 } = {}) {
  const conditions = [];
  const params = [];

  if (status && status !== 'all') {
    conditions.push('q.status = ?');
    params.push(status);
  }

  const whereClause = conditions.length ? `WHERE ${conditions.join(' AND ')}` : '';

  const countRow = db.prepare(`SELECT COUNT(*) AS total FROM photo_enrichment_queue q ${whereClause}`).get(...params);
  const total = Number(countRow?.total || 0);

  const query = `
    SELECT q.id AS queue_id, q.place_id, q.status, q.attempts, q.error_message, q.created_at, q.updated_at,
           p.title, p.address, p.category, p.image_urls
    FROM photo_enrichment_queue q
    LEFT JOIN scraped_places p ON p.id = q.place_id
    ${whereClause}
    ORDER BY q.id DESC
    LIMIT ? OFFSET ?
  `;

  const rows = db.prepare(query).all(...params, Number(limit), Number(offset));
  const items = rows.map((r) => {
    const item = toCamel(r);
    item.imageUrls = parseJson(item.imageUrls);
    return item;
  });

  return { items, total, limit: Number(limit), offset: Number(offset) };
}

/** Clear completed/failed queue items */
export function clearPhotoQueue(status = 'completed') {
  if (status === 'all') {
    const res = db.prepare('DELETE FROM photo_enrichment_queue').run();
    return res.changes;
  }
  const res = db.prepare('DELETE FROM photo_enrichment_queue WHERE status = ?').run(status);
  return res.changes;
}

export function getPhotoWorkerStats() {
  const row = db.prepare(`
    SELECT
      (SELECT COUNT(*) FROM photo_enrichment_queue WHERE status = 'pending') AS pendingCount,
      (SELECT COUNT(*) FROM photo_enrichment_queue WHERE status = 'processing') AS processingCount,
      (SELECT COUNT(*) FROM photo_enrichment_queue WHERE status = 'completed') AS completedCount,
      (SELECT COUNT(*) FROM photo_enrichment_queue WHERE status = 'failed') AS failedCount,
      (SELECT COUNT(*) FROM scraped_places WHERE image_urls IS NULL OR image_urls = '[]' OR image_urls = '') AS zeroPhotoPlaces
  `).get();
  return {
    pending: Number(row.pendingCount || 0),
    processing: Number(row.processingCount || 0),
    completed: Number(row.completedCount || 0),
    failed: Number(row.failedCount || 0),
    zeroPhotos: Number(row.zeroPhotoPlaces || 0),
  };
}

/** Get all scraped places across tasks with optional filters (search, category, taskId, photoFilter, limit, offset) */
export function getAllPlaces({ search = '', category = '', taskId = null, photoFilter = 'all', limit = 100, offset = 0 } = {}) {
  const conditions = [];
  const params = [];

  if (search && typeof search === 'string' && search.trim()) {
    const term = `%${search.trim()}%`;
    conditions.push('(p.title LIKE ? OR p.address LIKE ? OR p.phone LIKE ? OR p.description LIKE ?)');
    params.push(term, term, term, term);
  }

  if (category && typeof category === 'string' && category.trim() && category !== 'all') {
    conditions.push('p.category LIKE ?');
    params.push(`%${category.trim()}%`);
  }

  if (taskId && (Number.isInteger(Number(taskId)) || (typeof taskId === 'string' && taskId.trim() && taskId !== 'all'))) {
    conditions.push('p.task_id = ?');
    params.push(Number(taskId));
  }

  const whereClause = conditions.length ? `WHERE ${conditions.join(' AND ')}` : '';
  
  const query = `
    SELECT p.*, t.query AS task_query, t.created_at AS task_created_at
    FROM scraped_places p
    LEFT JOIN scrape_tasks t ON t.id = p.task_id
    ${whereClause}
    ORDER BY p.id DESC
  `;

  const allRows = db.prepare(query).all(...params).map((row) => {
    const place = toCamel(row);
    place.imageUrls = parseJson(place.imageUrls);
    return place;
  });

  // Apply in-memory photo filter if requested
  const filtered = allRows.filter((p) => {
    const imgList = Array.isArray(p.imageUrls) ? p.imageUrls : [];
    if (photoFilter === 'zero') return imgList.length === 0;
    if (photoFilter === 'low') return imgList.length <= 1;
    return true;
  });

  const total = filtered.length;
  const places = filtered.slice(Number(offset), Number(offset) + Number(limit));

  return { places, total, limit: Number(limit), offset: Number(offset) };
}

/** Get distinct categories of scraped places */
export function getCategories() {
  const rows = db.prepare(`
    SELECT DISTINCT category 
    FROM scraped_places 
    WHERE category IS NOT NULL AND category != '' 
    ORDER BY category ASC
  `).all();
  return rows.map(r => r.category).filter(Boolean);
}

// ─── Stats ──────────────────────────────────────────────────────
export function getStats() {
  const row = db
    .prepare(
      `SELECT
         (SELECT COUNT(*) FROM apify_accounts) AS totalAccounts,
         (SELECT COUNT(*) FROM apify_accounts WHERE status = 'active') AS activeAccounts,
         (SELECT COUNT(*) FROM scrape_tasks) AS totalScrapes,
         (SELECT COALESCE(SUM(items_count), 0) FROM scrape_tasks) AS totalPlaces,
         (SELECT COUNT(*) FROM scrape_tasks WHERE status = 'completed') AS completedScrapes`
    )
    .get();
  return {
    totalAccounts: Number(row.totalAccounts),
    activeAccounts: Number(row.activeAccounts),
    totalScrapes: Number(row.totalScrapes),
    totalPlaces: Number(row.totalPlaces),
    completedScrapes: Number(row.completedScrapes),
  };
}

// ─── Migration from accounts.json / .env ────────────────────────
const loadAccountsJson = () => {
  const candidates = [path.join(ROOT, 'accounts.json'), path.join(process.cwd(), 'accounts.json')];
  for (const file of candidates) {
    if (!existsSync(file)) continue;
    try {
      const parsed = JSON.parse(readFileSync(file, 'utf8'));
      if (!Array.isArray(parsed)) continue;
      return parsed
        .filter((a) => a && typeof a.token === 'string' && a.token)
        .map((a, i) => ({
          email: typeof a.email === 'string' && a.email ? a.email : `account-${i + 1}`,
          password: a.password ?? null,
          token: a.token,
          userId: a.userId ?? a.user_id ?? null,
          username: a.username ?? null,
          status: a.status ?? 'active',
          usageCount: a.usageCount ?? a.usage_count ?? 0,
          createdAt: a.createdAt ?? a.created_at ?? null,
        }));
    } catch {
      // malformed JSON — try the next candidate
    }
  }
  return [];
};

const readEnvToken = (file) => {
  try {
    const text = readFileSync(file, 'utf8');
    const match = text.match(/^\s*APIFY_TOKEN\s*=\s*["']?([^"'\r\n]+)["']?\s*$/m);
    return match ? match[1].trim() : null;
  } catch {
    return null;
  }
};

/** Import legacy accounts from accounts.json / .env once, when the table is empty. */
function migrateLegacyData() {
  const { c: count } = db.prepare('SELECT COUNT(*) AS c FROM apify_accounts').get();
  if (count > 0) return;

  const accounts = loadAccountsJson();
  const envToken = readEnvToken(path.join(ROOT, '.env')) || process.env.APIFY_TOKEN || null;
  if (envToken && !accounts.some((a) => a.token === envToken)) {
    accounts.push({ email: '', password: null, token: envToken, userId: null, username: 'env', status: 'active' });
  }
  if (accounts.length === 0) return;

  // created_at is only included when the source record actually has a value.
  const insert = (acc) => {
    const status = ACCOUNT_STATUSES.has(acc.status) ? acc.status : 'active';
    const usageCount = Number.isInteger(Number(acc.usageCount)) ? Number(acc.usageCount) : 0;
    if (typeof acc.createdAt === 'string' && acc.createdAt) {
      return db.prepare(
        `INSERT INTO apify_accounts (email, password, token, user_id, username, status, usage_count, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)`
      ).run(acc.email || '', acc.password || null, acc.token, acc.userId || null, acc.username || null, status, usageCount, acc.createdAt);
    }
    return db.prepare(
      `INSERT INTO apify_accounts (email, password, token, user_id, username, status, usage_count)
       VALUES (?, ?, ?, ?, ?, ?, ?)`
    ).run(acc.email || '', acc.password || null, acc.token, acc.userId || null, acc.username || null, status, usageCount);
  };
  db.exec('BEGIN');
  try {
    for (const acc of accounts) insert(acc);
    db.exec('COMMIT');
  } catch (err) {
    db.exec('ROLLBACK');
    throw err;
  }
}

try {
  migrateLegacyData();
} catch (err) {
  console.warn(`[db] Legacy data migration skipped: ${err.message}`);
}

export function close() {
  try {
    db.close();
  } catch {
    // already closed
  }
}

export default {
  getAccounts,
  getAccountById,
  getAccountByToken,
  getAccountByEmail,
  saveAccount,
  updateAccount,
  deleteAccount,
  getActiveAccounts,
  getNextRotatedAccount,
  incrementAccountUsage,
  setAccountStatus,
  createTask,
  updateTask,
  getTasks,
  getTaskById,
  deleteTask,
  saveTaskPlaces,
  getTaskPlaces,
  getStats,
  close,
};
