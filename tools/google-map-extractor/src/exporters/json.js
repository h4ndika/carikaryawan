// JSON exporter
import { writeFile } from 'node:fs/promises';

export async function exportToJson(items, outputPath) {
  const list = Array.isArray(items) ? items : [];
  await writeFile(outputPath, JSON.stringify(list, null, 2), 'utf8');
  return { filePath: outputPath, count: list.length };
}