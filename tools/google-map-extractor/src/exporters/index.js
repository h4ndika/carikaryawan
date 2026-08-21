import path from 'node:path';
import { mkdir } from 'node:fs/promises';
import { normalizeItems } from './normalize.js';
import { exportToCsv } from './csv.js';
import { exportToExcel } from './excel.js';
import { exportToJson } from './json.js';
import { exportToXml } from './xml.js';

export async function exportData({
  items,
  format = 'all',
  outputDir = './output',
  querySlug = 'places',
}) {
  const requestedFormat = (format || '').toLowerCase().trim();

  // If no format requested or explicitly 'none', skip file generation
  if (!requestedFormat || requestedFormat === 'none') {
    return {
      totalItems: items?.length || 0,
      files: [],
    };
  }

  const resolvedDir = path.resolve(process.cwd(), outputDir);
  await mkdir(resolvedDir, { recursive: true });

  const normalized = normalizeItems(items);
  const timestamp = Date.now();
  const safeSlug = (querySlug || 'places')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '') || 'places';

  const formatList = requestedFormat === 'all'
    ? ['csv', 'xlsx', 'json', 'xml']
    : requestedFormat.split(',').map((f) => f.trim());

  const results = [];

  if (formatList.includes('csv')) {
    const csvPath = path.join(resolvedDir, `${safeSlug}_${timestamp}.csv`);
    const res = await exportToCsv(normalized, csvPath);
    results.push({ format: 'csv', ...res });
  }

  if (formatList.includes('xlsx') || formatList.includes('excel')) {
    const xlsxPath = path.join(resolvedDir, `${safeSlug}_${timestamp}.xlsx`);
    const res = await exportToExcel(normalized, xlsxPath);
    results.push({ format: 'xlsx', ...res });
  }

  if (formatList.includes('json')) {
    const jsonPath = path.join(resolvedDir, `${safeSlug}_${timestamp}.json`);
    const res = await exportToJson(normalized, jsonPath);
    results.push({ format: 'json', ...res });
  }

  if (formatList.includes('xml')) {
    const xmlPath = path.join(resolvedDir, `${safeSlug}_${timestamp}.xml`);
    const res = await exportToXml(normalized, xmlPath);
    results.push({ format: 'xml', ...res });
  }

  return {
    totalItems: normalized.length,
    files: results,
  };
}
