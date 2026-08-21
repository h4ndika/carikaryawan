// Excel exporter using exceljs
import ExcelJS from 'exceljs';
import path from 'node:path';

const COLUMNS = [
  { key: 'title', header: 'Title', width: 30 },
  { key: 'category', header: 'Category', width: 20 },
  { key: 'rating', header: 'Rating', width: 10 },
  { key: 'reviewsCount', header: 'Reviews', width: 12 },
  { key: 'phone', header: 'Phone', width: 18 },
  { key: 'website', header: 'Website', width: 40 },
  { key: 'address', header: 'Address', width: 40 },
  { key: 'latitude', header: 'Latitude', width: 12 },
  { key: 'longitude', header: 'Longitude', width: 12 },
  { key: 'price', header: 'Price', width: 10 },
  { key: 'openingHours', header: 'Opening Hours', width: 50 },
  { key: 'description', header: 'Description', width: 50 },
  { key: 'facilities', header: 'Facilities', width: 40 },
  { key: 'googleMapsUrl', header: 'Maps URL', width: 40 },
  { key: 'placeId', header: 'Place ID', width: 30 },
  { key: 'cid', header: 'CID', width: 20 },
];

export async function exportToExcel(items, outputPath) {
  const list = Array.isArray(items) ? items : [];
  
  const workbook = new ExcelJS.Workbook();
  const worksheet = workbook.addWorksheet('Places');

  // Define columns
  worksheet.columns = COLUMNS;

  // Style header row
  const headerRow = worksheet.getRow(1);
  headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
  headerRow.fill = {
    type: 'pattern',
    pattern: 'solid',
    fgColor: { argb: 'FF1A73E8' },
  };
  headerRow.alignment = { vertical: 'middle', horizontal: 'center' };
  headerRow.height = 20;

  // Add data rows
  list.forEach((item) => {
    const row = {};
    COLUMNS.forEach((col) => {
      row[col.key] = formatField(item[col.key]);
    });
    worksheet.addRow(row);
  });

  // Apply borders and auto-fit columns
  worksheet.eachRow((row, rowNumber) => {
    row.eachCell((cell, colNumber) => {
      cell.border = {
        top: { style: 'thin' },
        left: { style: 'thin' },
        bottom: { style: 'thin' },
        right: { style: 'thin' },
      };
    });
  });

  // Auto-fit column widths with max width constraint
  worksheet.columns.forEach((column) => {
    if (!column.width || column.width > 50) {
      column.width = 50;
    }
  });

  await workbook.xlsx.writeFile(outputPath);
  return { filePath: outputPath, count: list.length };
}

function formatField(value) {
  if (value === null || value === undefined) return '';
  if (Array.isArray(value)) return value.join('; ');
  return value;
}
