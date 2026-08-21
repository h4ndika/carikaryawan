// XML exporter with proper entity escaping
import { writeFile } from 'node:fs/promises';

export async function exportToXml(items, outputPath) {
  const list = Array.isArray(items) ? items : [];
  const lines = ['<?xml version="1.0" encoding="UTF-8"?>', '<businesses>'];
  for (const item of list) {
    lines.push('  <business>');
    for (const [key, value] of Object.entries(item)) {
      const tag = escapeXmlTag(key);
      const content = escapeXmlValue(formatField(value));
      lines.push(`    <${tag}>${content}</${tag}>`);
    }
    lines.push('  </business>');
  }
  lines.push('</businesses>');
  await writeFile(outputPath, lines.join('\n'), 'utf8');
  return { filePath: outputPath, count: list.length };
}

function escapeXmlValue(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function escapeXmlTag(tag) {
  // Ensure valid XML tag name: replace invalid chars with underscore
  return String(tag).replace(/[^a-zA-Z0-9_.-]/g, '_');
}

function formatField(value) {
  if (value === null || value === undefined) return '';
  if (Array.isArray(value)) return value.join('; ');
  return value;
}