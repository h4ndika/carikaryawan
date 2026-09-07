#!/usr/bin/env node

import { Command } from 'commander';
import { scrapeJobs, ENGINE_GOOGLE_JOBS, ENGINE_INSTAGRAM } from './scraper.js';

const program = new Command();

program
  .name('disekitar-job-extractor')
  .description('Ekstraktor Lowongan Kerja Lokal untuk DiSekitar (Google Jobs & Instagram)')
  .version('1.0.0');

program
  .command('scrape')
  .description('Scrape lowongan kerja berdasarkan kata kunci atau username akun Instagram')
  .option('-e, --engine <engine>', 'Pilihan engine: "google" atau "instagram"', 'google')
  .option('-q, --query <queries...>', 'Kata kunci pencarian atau username Instagram (tanpa @)', ['lowongan barista bandung'])
  .option('-m, --max <number>', 'Maksimal jumlah lowongan yang diambil', (val) => parseInt(val, 10), 20)
  .option('-t, --token <token>', 'Apify API Token (opsional, fallback ke .env)')
  .option('-o, --output <dir>', 'Direktori output penyimpanan JSON', './output')
  .action(async (options) => {
    try {
      const selectedEngine = options.engine === 'instagram' ? ENGINE_INSTAGRAM : ENGINE_GOOGLE_JOBS;
      
      console.log(`\n========================================`);
      console.log(`🔍 DiSekitar Job Extractor CLI`);
      console.log(`========================================`);
      console.log(`Engine : ${selectedEngine}`);
      console.log(`Query  : ${options.query}`);
      console.log(`Limit  : ${options.max}`);
      console.log(`Output : ${options.output}`);

      const result = await scrapeJobs({
        engine: selectedEngine,
        query: options.query,
        maxItems: options.max,
        token: options.token,
        outputDir: options.output
      });

      console.log(`\n✨ Sukses! ${result.count} data lowongan siap diimpor ke DiSekitar.`);
      console.log(`File: ${result.filePath}\n`);
    } catch (err) {
      console.error(`\n❌ Error: ${err.message}\n`);
      process.exit(1);
    }
  });

program.parse(process.argv);

if (process.argv.length <= 2) {
  program.help();
}
