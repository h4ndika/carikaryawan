// RFC 4180 CSV exporter
import { writeFile } from 'node:fs/promises';
import path from 'node:path';

const DEFAULT_COLUMNS = [
  'title',
  'category',
  'rating',
  'reviewsCount',
  'phone',
  'website',
  'address',
  'latitude',
  'longitude',
  'price',
  'openingHours',
  'description',
  'facilities',
  'googleMapsUrl',
  'placeId',
  'cid',
];

export async function exportToCsv(items, outputPath, columns = DEFAULT_COLUMNS) {
  const list = Array.isArray(items) ? items : [];
  const headerRow = columns.map(escapeCsvField).join(',');
  const rows = list.map((item) =>
    columns.map((col) => escapeCsvField(formatField(item[col], col))).join(','),
  );
  const csv = [headerRow, ...rows].join('\r\n');
  await writeFile(outputPath, csv, 'utf8');
  return { filePath: outputPath, count: list.length };
}

function escapeCsvField(value) {
  if (value === null || value === undefined) return '';
  const str = String(value);
  // RFC 4180: wrap in double quotes if contains comma, quote, CR, or LF
  if (/[",\r\n]/.test(str)) {
    return `"${str.replace(/"/g, '""')}"`;
  }
  return str;
}

function formatField(value, key) {
  if (value === null || value === undefined) return '';
  if (Array.isArray(value)) return value.join('; ');
  return value;
}