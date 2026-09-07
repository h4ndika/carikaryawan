#!/usr/bin/env node

import { Command } from 'commander';
import inquirer from 'inquirer';
import 'dotenv/config';
import { promises as fs } from 'node:fs';
import path from 'node:path';
import { logger } from './utils/logger.js';
import { scrapeGooglePlaces } from './scraper.js';
import { createApifyAccount } from './accountCreator.js';
import { createApifyAccountWithGithub, loadGithubAccounts, getUnregisteredGithubAccounts } from './accountCreatorGithub.js';
import { exportData } from './exporters/index.js';
import { startServer } from './server/index.js';
import { getAccounts, getStats } from './db/index.js';

const program = new Command();

program
  .name('disekitar-gmaps-v2')
  .description('Google Maps Scraper & Apify Automation CLI (Node.js Edition)')
  .version('2.0.0');

// ─── COMMAND: DASHBOARD ───────────────────────────────────────
program
  .command('dashboard')
  .description('Start the Web Dashboard & API Server')
  .option('-p, --port <number>', 'Server port', (val) => parseInt(val, 10), 3000)
  .action((options) => {
    startServer(options.port);
  });

// ─── COMMAND: SCRAPE ──────────────────────────────────────────
program
  .command('scrape')
  .description('Scrape business data from Google Maps using Apify actor')
  .option('-q, --query <queries...>', 'Search query or multiple queries', ['kafe di bandung'])
  .option('-l, --location <location>', 'Location bounding filter (e.g. "Bandung, Indonesia")')
  .option('-m, --max <number>', 'Maximum places per search query', (val) => parseInt(val, 10), 20)
  .option('--lang <language>', 'Google Maps language code', 'id')
  .option('--country <countryCode>', 'Country code', 'id')
  .option('--reviews', 'Include reviews in scraping', false)
  .option('--no-images', 'Do not scrape image URLs')
  .option('-f, --format <format>', 'Export format: csv, xlsx, json, xml, all', 'all')
  .option('-o, --output <dir>', 'Output directory for files', './output')
  .option('-t, --token <token>', 'Apify API token override')
  .option('-a, --account <id>', 'Specific Apify account ID from database to use')
  .option('--rotate', 'Auto-rotate active accounts from SQLite database', false)
  .action(async (options) => {
    logger.banner('Google Maps Extractor v2 (Apify Engine)');

    try {
      const queries = Array.isArray(options.query) ? options.query : [options.query];
      const result = await scrapeGooglePlaces({
        token: options.token,
        accountId: options.account ? parseInt(options.account, 10) : undefined,
        rotateAccount: options.rotate,
        queries,
        location: options.location,
        maxPlaces: options.max,
        language: options.lang,
        countryCode: options.country,
        includeReviews: options.reviews,
        includeImages: options.images,
        format: options.format,
        outputDir: options.output,
      });

      logger.success(`Scrape completed! Task #${result.taskId} - ${result.count} places.`);
      if (result.files?.length) {
        console.log('\nGenerated Files:');
        result.files.forEach((f) => console.log(`  📄 [${f.format.toUpperCase()}] ${f.filePath} (${f.count} items)`));
      }
    } catch (err) {
      logger.error(`Scraping failed: ${err.message}`);
      process.exit(1);
    }
  });

// ─── COMMAND: ACCOUNT CREATOR GITHUB ───────────────────────────
program
  .command('create-account-github')
  .description('Automate creating Apify account via GitHub OAuth & Gmail API (Bypass Captcha)')
  .option('--headless', 'Run browser in headless mode', false)
  .option('--no-env', 'Do not auto-update .env with generated token')
  .action(async (options) => {
    try {
      await createApifyAccountWithGithub({
        headless: Boolean(options.headless),
        saveToEnv: options.env !== false,
      });
    } catch (err) {
      logger.error(`GitHub Account creation failed: ${err.message}`);
      process.exit(1);
    }
  });

// ─── COMMAND: ACCOUNT CREATOR EMAIL ────────────────────────────
program
  .command('create-account')
  .description('Automate creating a free Apify account via Email & Gmail API')
  .option('--headless', 'Run browser in headless mode', false)
  .option('--no-env', 'Do not auto-update .env with generated token')
  .action(async (options) => {
    try {
      await createApifyAccount({
        headless: Boolean(options.headless),
        saveToEnv: options.env !== false,
      });
    } catch (err) {
      logger.error(`Account creation failed: ${err.message}`);
      process.exit(1);
    }
  });

// ─── COMMAND: ACCOUNTS LIST ───────────────────────────────────
program
  .command('accounts')
  .description('List all saved Apify accounts from SQLite database')
  .action(() => {
    logger.banner('Saved Apify Accounts (SQLite Database)');
    try {
      const accounts = getAccounts();

      if (!Array.isArray(accounts) || accounts.length === 0) {
        logger.warn('No accounts saved yet. Run "npm run create-account" or use the Web Dashboard.');
        return;
      }

      console.log(`Found ${accounts.length} saved account(s):\n`);
      accounts.forEach((acc, i) => {
        const maskToken = acc.token ? `${acc.token.slice(0, 10)}...${acc.token.slice(-4)}` : '-';
        console.log(`[#${acc.id}] ${acc.email} (${acc.status.toUpperCase()})`);
        console.log(`    Token:        ${maskToken}`);
        console.log(`    Usage Count:  ${acc.usage_count}`);
        console.log(`    Last Used:    ${acc.last_used_at || 'Never'}`);
        console.log(`    Created At:   ${acc.created_at || '-'}\n`);
      });
    } catch (err) {
      logger.error(`Could not read database accounts: ${err.message}`);
    }
  });

// ─── INTERACTIVE MODE (DEFAULT WHEN NO ARGS) ───────────────────
async function runInteractiveMenu() {
  logger.banner('Google Maps Extractor v2 — Interactive CLI');

  const stats = getStats();
  logger.info(`Database: ${stats.totalAccounts} accounts (${stats.activeAccounts} active) | ${stats.totalScrapes} tasks | ${stats.totalPlaces} places\n`);

  const { action } = await inquirer.prompt([
    {
      type: 'list',
      name: 'action',
      message: 'Select action to perform:',
      choices: [
        { name: '🌐 1. Start Web Dashboard (Interactive UI & Live Logs)', value: 'dashboard' },
        { name: '🚀 2. Scrape Google Maps Places (via Apify)', value: 'scrape' },
        { name: '🐙 3. Auto-Create Apify Account (via GitHub OAuth - No Captcha)', value: 'create_account_github' },
        { name: '📧 4. Auto-Create Apify Account (via Email & Gmail API)', value: 'create_account_email' },
        { name: '📋 5. View Saved Apify Accounts (SQLite)', value: 'list_accounts' },
        { name: '❌ 6. Exit', value: 'exit' },
      ],
    },
  ]);

  if (action === 'exit') {
    logger.info('Goodbye!');
    process.exit(0);
  }

  if (action === 'dashboard') {
    startServer(process.env.PORT || 3000);
    return;
  }

  if (action === 'list_accounts') {
    const accounts = getAccounts();
    console.log('\n' + JSON.stringify(accounts, null, 2) + '\n');
    return runInteractiveMenu();
  }

  if (action === 'create_account_github') {
    let unregistered = [];
    let allGithub = [];
    try {
      allGithub = await loadGithubAccounts();
      unregistered = await getUnregisteredGithubAccounts();
    } catch (err) {
      logger.error(`Gagal membaca file akun GitHub: ${err.message}`);
      return runInteractiveMenu();
    }

    const totalCount = allGithub.length;
    const unregisteredCount = unregistered.length;
    const registeredCount = totalCount - unregisteredCount;

    console.log('\n' + '─'.repeat(55));
    logger.info(`📊 Info Akun GitHub (src/github_accounts.txt):`);
    logger.info(`   • Total akun di file   : ${totalCount}`);
    logger.info(`   • Sudah terdaftar Apify: ${registeredCount}`);
    logger.info(`   • Belum registrasi     : ${unregisteredCount}`);
    console.log('─'.repeat(55) + '\n');

    if (unregisteredCount === 0) {
      logger.warn('Semua akun GitHub dari file github_accounts.txt sudah terdaftar di database Apify!');
      return runInteractiveMenu();
    }

    const { accountCount, headless } = await inquirer.prompt([
      {
        type: 'input',
        name: 'accountCount',
        message: `Berapa akun yang ingin dibuat? (1 - ${unregisteredCount}):`,
        default: '1',
        validate: (val) => {
          const num = parseInt(val, 10);
          if (isNaN(num) || num < 1 || num > unregisteredCount) {
            return `Masukkan angka valid antara 1 dan ${unregisteredCount}!`;
          }
          return true;
        },
      },
      {
        type: 'confirm',
        name: 'headless',
        message: 'Run in headless mode? (No is recommended to see automated browser flow)',
        default: false,
      },
    ]);

    const toCreate = parseInt(accountCount, 10);
    logger.info(`🚀 Memulai pendaftaran ${toCreate} akun Apify via GitHub OAuth...\n`);

    let successCount = 0;
    for (let i = 0; i < toCreate; i++) {
      const targetAcc = unregistered[i];
      logger.banner(`[Akun ${i + 1}/${toCreate}] Mendaftarkan: ${targetAcc.username} (${targetAcc.email})`);
      try {
        await createApifyAccountWithGithub({ headless, account: targetAcc });
        successCount++;
        logger.success(`✔ [Akun ${i + 1}/${toCreate}] Berhasil membuat akun Apify untuk ${targetAcc.email}`);
      } catch (err) {
        logger.error(`✖ [Akun ${i + 1}/${toCreate}] Gagal untuk ${targetAcc.email}: ${err.message}`);
      }

      if (i < toCreate - 1) {
        logger.info('Menunggu jeda 5 detik sebelum akun berikutnya...');
        await new Promise((resolve) => setTimeout(resolve, 5000));
      }
    }

    logger.banner(`SELESAI! Berhasil mendaftarkan ${successCount} dari ${toCreate} akun Apify.`);
    return runInteractiveMenu();
  }

  if (action === 'create_account_email') {
    const { headless } = await inquirer.prompt([
      {
        type: 'confirm',
        name: 'headless',
        message: 'Run in headless mode? (No is recommended to visually solve Cloudflare if prompted)',
        default: false,
      },
    ]);

    await createApifyAccount({ headless });
    return runInteractiveMenu();
  }

  if (action === 'scrape') {
    const accounts = getAccounts();
    const accountChoices = [
      { name: '🔄 Auto-Rotate Accounts (Next available active account)', value: 'rotate' },
    ];

    accounts.forEach((acc) => {
      accountChoices.push({
        name: `🔑 #${acc.id} ${acc.email} (${acc.status} | used: ${acc.usage_count}x)`,
        value: acc.id,
      });
    });

    accountChoices.push({ name: '⚙️ Use Default Token from .env', value: 'env' });

    const answers = await inquirer.prompt([
      {
        type: 'input',
        name: 'query',
        message: 'Search query (e.g. "kafe di bandung", "hotel di jakarta", "penitipan koper dekat stasiun bandung"):',
        default: 'kafe di bandung',
      },
      {
        type: 'input',
        name: 'location',
        message: 'Location filter (optional, press enter to skip):',
        default: '',
      },
      {
        type: 'number',
        name: 'maxPlaces',
        message: 'Maximum places to scrape:',
        default: 20,
      },
      {
        type: 'list',
        name: 'accountChoice',
        message: 'Select Apify Account to use:',
        choices: accountChoices,
      },
      {
        type: 'list',
        name: 'format',
        message: 'Export file format:',
        choices: [
          { name: 'All Formats (CSV, Excel, JSON, XML)', value: 'all' },
          { name: 'Excel (.xlsx)', value: 'xlsx' },
          { name: 'CSV (.csv)', value: 'csv' },
          { name: 'JSON (.json)', value: 'json' },
          { name: 'XML (.xml)', value: 'xml' },
        ],
        default: 'all',
      },
    ]);

    try {
      const scrapeOpts = {
        queries: [answers.query],
        location: answers.location,
        maxPlaces: answers.maxPlaces,
        format: answers.format,
      };

      if (answers.accountChoice === 'rotate') {
        scrapeOpts.rotateAccount = true;
      } else if (typeof answers.accountChoice === 'number') {
        scrapeOpts.accountId = answers.accountChoice;
      }

      const res = await scrapeGooglePlaces(scrapeOpts);

      logger.success(`Export completed successfully! Task #${res.taskId}`);
      if (res.files?.length) {
        res.files.forEach((f) => {
          console.log(`  📄 [${f.format.toUpperCase()}] ${f.filePath} (${f.count} items)`);
        });
      }
    } catch (err) {
      logger.error(`Scrape failed: ${err.message}`);
    }

    return runInteractiveMenu();
  }
}

// Entrypoint dispatcher
if (process.argv.length <= 2) {
  runInteractiveMenu().catch((err) => {
    logger.error(`Error: ${err.message}`);
    process.exit(1);
  });
} else {
  program.parse(process.argv);
}
