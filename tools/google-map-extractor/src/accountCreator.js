import { promises as fs } from 'node:fs';
import fsSync from 'node:fs';
import path from 'node:path';
import puppeteer from 'puppeteer-extra';
import StealthPlugin from 'puppeteer-extra-plugin-stealth';
import { google } from 'googleapis';
import 'dotenv/config';
import { logger } from './utils/logger.js';
import { saveAccount } from './db/index.js';
import { authorize } from './auth.js';
import { waitForApifyEmail } from './waitForOtp.js';

// Apply stealth plugin to evade Cloudflare / Turnstile detection
puppeteer.use(StealthPlugin());

// Helper sleep function for modern Puppeteer
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const DOMAINS = ['disekitar.com', 'kirimpaket.id'];

/**
 * Generate random custom email from disekitar.com or kirimpaket.id
 */
export function generateRandomEmail(domain) {
  const chosenDomain = domain || DOMAINS[Math.floor(Math.random() * DOMAINS.length)];
  const names = [
    'alex', 'jordan', 'taylor', 'morgan', 'casey', 'riley', 'jessie', 'jamie',
    'jack', 'john', 'sara', 'lisa', 'mary', 'emma', 'ava', 'mia', 'chloe',
    'grace', 'zoe', 'olivia', 'amelia', 'isabella', 'sophia', 'emily', 'charlotte',
    'abigail', 'ella', 'hannah', 'victoria', 'lauren', 'anna', 'amanda', 'ashley',
    'sam', 'tom', 'david', 'michael', 'james', 'robert', 'william', 'richard',
  ];
  const randomName = names[Math.floor(Math.random() * names.length)];
  const randomNum = Math.floor(Math.random() * 90000) + 10000;
  return `${randomName}${randomNum}@${chosenDomain}`;
}

/**
 * Generate strong random password
 */
export function generateRandomPassword() {
  const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  const lower = 'abcdefghijkmnpqrstuvwxyz';
  const digits = '23456789';
  const symbols = '!@#$%&*';
  const pick = (s) => s[Math.floor(Math.random() * s.length)];
  let pwd = pick(upper) + pick(lower) + pick(digits) + pick(symbols);
  const all = upper + lower + digits + symbols;
  for (let i = 0; i < 10; i++) pwd += pick(all);
  return pwd.split('').sort(() => Math.random() - 0.5).join('').slice(0, 16);
}

/**
 * Detect installed Chrome/Edge/Brave executable path on Windows, macOS, or Linux
 */
export function findSystemBrowserExecutable() {
  if (process.env.PUPPETEER_EXECUTABLE_PATH && fsSync.existsSync(process.env.PUPPETEER_EXECUTABLE_PATH)) {
    return process.env.PUPPETEER_EXECUTABLE_PATH;
  }

  const localAppData = process.env.LOCALAPPDATA || '';
  const programFiles = process.env.ProgramFiles || 'C:\\Program Files';
  const programFilesX86 = process.env['ProgramFiles(x86)'] || 'C:\\Program Files (x86)';

  const candidatePaths = [
    path.join(programFiles, 'Google', 'Chrome', 'Application', 'chrome.exe'),
    path.join(programFilesX86, 'Google', 'Chrome', 'Application', 'chrome.exe'),
    path.join(localAppData, 'Google', 'Chrome', 'Application', 'chrome.exe'),
    path.join(programFiles, 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
    path.join(programFilesX86, 'Microsoft', 'Edge', 'Application', 'msedge.exe'),
    path.join(programFiles, 'BraveSoftware', 'Brave-Browser', 'Application', 'brave.exe'),
    path.join(programFilesX86, 'BraveSoftware', 'Brave-Browser', 'Application', 'brave.exe'),
    path.join(localAppData, 'BraveSoftware', 'Brave-Browser', 'Application', 'brave.exe'),
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
    '/Applications/Brave Browser.app/Contents/MacOS/Brave Browser',
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
  ];

  for (const p of candidatePaths) {
    try {
      if (p && fsSync.existsSync(p)) return p;
    } catch {}
  }

  return undefined;
}

/**
 * Automates creating an Apify account via Gmail API, verifying it, and extracting API Token
 *
 * @param {Object} [options={}]
 * @param {string} [options.domain] - 'disekitar.com' or 'kirimpaket.id'
 * @param {boolean} [options.headless=false] - Use headful browser to reduce Cloudflare captcha friction
 * @param {boolean} [options.saveToEnv=true] - Update .env with new token
 * @param {string} [options.accountsFile='accounts.json'] - Path to save account history
 * @param {Function} [options.onProgress] - Progress callback
 * @returns {Promise<{success: boolean, email: string, password: string, token: string, user: Object}>}
 */
export async function createApifyAccount(options = {}) {
  const headless = options.headless ?? false;
  const saveToEnv = options.saveToEnv ?? true;
  const accountsFile = options.accountsFile || path.resolve(process.cwd(), 'accounts.json');

  const report = (level, message, data = null) => {
    if (level === 'error') logger.error(message);
    else if (level === 'success') logger.success(message);
    else if (level === 'warn') logger.warn(message);
    else logger.info(message);

    if (typeof options.onProgress === 'function') {
      try {
        options.onProgress({ level, message, data, timestamp: new Date().toISOString() });
      } catch {}
    }
  };

  logger.banner('Apify Account Creator (Gmail API Edition)');

  // Step 1: Initialize Gmail API Client
  logger.step(1, 6, 'Authorizing Gmail API client...');
  report('info', 'Connecting to Gmail API via credentials.json & token.json...');
  const auth = await authorize();
  const gmail = google.gmail({ version: 'v1', auth });
  report('success', 'Gmail API authorized successfully.');

  // Step 2: Generate random email & password
  const email = generateRandomEmail(options.domain);
  const password = generateRandomPassword();
  const firstName = email.split('@')[0].replace(/[0-9]/g, '');
  const fullName = firstName.charAt(0).toUpperCase() + firstName.slice(1) + ' ' + (Math.floor(Math.random() * 900) + 100);

  logger.step(2, 6, `Generated credentials for ${email}`);
  report('info', `Target email: ${email} | Domain: ${email.split('@')[1]}`);

  // Step 3: Launch Puppeteer with anti-detection stealth
  logger.step(3, 6, 'Launching browser with Puppeteer stealth...');
  const executablePath = findSystemBrowserExecutable();
  if (executablePath) {
    logger.info(`Using system browser at: ${executablePath}`);
  }

  const launchOptions = {
    headless: headless ? 'new' : false,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--disable-dev-shm-usage',
      '--disable-accelerated-2d-canvas',
      '--no-first-run',
      '--no-zygote',
      '--window-size=1280,850',
    ],
    defaultViewport: { width: 1280, height: 850 },
  };

  if (executablePath) {
    launchOptions.executablePath = executablePath;
  }

  const browser = await puppeteer.launch(launchOptions);

  try {
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'en-US,en;q=0.9' });

    // Step 4: Fill Sign Up Form (Email -> Next -> Password -> Sign up)
    logger.step(4, 6, 'Navigating to Apify sign-up page (https://console.apify.com/sign-up)...');
    report('info', 'Navigating to https://console.apify.com/sign-up...');
    await page.goto('https://console.apify.com/sign-up', {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });

    await sleep(2000);

    // 1. Input Email
    report('info', `Typing email: ${email}`);
    const emailSelector = 'input[data-test="email"], input[id="email"], input[name="email"], input[type="email"]';
    await page.waitForSelector(emailSelector, { timeout: 25000 });
    await page.type(emailSelector, email, { delay: 40 });
    await sleep(600);

    // 2. Click Next / Submit Email
    report('info', 'Submitting email...');
    const nextBtnSelector = 'button[data-test="submit-email"], button[type="submit"]';
    await page.waitForSelector(nextBtnSelector, { timeout: 10000 });
    await page.click(nextBtnSelector);
    await sleep(2000);

    // 3. Input Password
    report('info', 'Typing password...');
    const passwordSelector = 'input[data-test="password"], input[id="password"], input[name="password"], input[type="password"]';
    await page.waitForSelector(passwordSelector, { timeout: 15000 });
    await page.type(passwordSelector, password, { delay: 40 });
    await sleep(600);

    // 4. Click Sign Up Submit button
    report('info', 'Submitting registration form...');
    const signUpBtnSelector = 'button[data-test="submit-button"], button#data-tracking-sign-up-direct, button[type="submit"]';
    await page.waitForSelector(signUpBtnSelector, { timeout: 10000 });
    await page.click(signUpBtnSelector);

    // Handle "Welcome to Apify" Onboarding Form (Full name + Continue)
    report('info', 'Checking for Welcome to Apify onboarding form...');
    await sleep(4500);

    try {
      const nameInputSelector = 'input[data-test="name-input"], input#name, input[name="name"]';
      const nameInput = await page.waitForSelector(nameInputSelector, { timeout: 10000 });
      if (nameInput) {
        report('info', `Filling full name: "${fullName}"...`);
        await page.type(nameInputSelector, fullName, { delay: 40 });
        await sleep(600);

        const continueBtnSelector = 'button[data-test="continue-button"], button[type="submit"]';
        await page.waitForSelector(continueBtnSelector, { timeout: 8000 });

        await page.waitForFunction(
          (sel) => {
            const btn = document.querySelector(sel);
            return btn && !btn.hasAttribute('disabled');
          },
          { timeout: 5000 },
          continueBtnSelector
        ).catch(() => {});

        await page.click(continueBtnSelector);
        report('info', 'Submitted onboarding continue button.');
        await sleep(3000);
      }
    } catch (e) {
      report('info', 'Onboarding name form not prompted or bypassed.');
    }

    // Step 5: Wait for verification email via Gmail API & navigate to it
    logger.step(5, 6, `Polling Gmail API for verification link sent to ${email}...`);
    report('info', `Waiting for email from hello@apify.com to ${email}...`);
    const verifyUrl = await waitForApifyEmail(gmail, email, { timeoutSeconds: 120, intervalSeconds: 3 });

    report('success', `Found verification link: ${verifyUrl}`);
    report('info', 'Opening verification link in browser...');
    await page.goto(verifyUrl, { waitUntil: 'networkidle2', timeout: 60000 });
    await sleep(4000);

    // Step 6: Claim $5 Free Credits (Gamification Modals)
    try {
      report('info', 'Checking for $5 free credit bonus in Help & Resources...');
      const helpBtnSelector = 'button[data-test="help-and-resources-button"], button[aria-label="Help & resources"], button.UsefulLinks-link';
      const helpBtn = await page.waitForSelector(helpBtnSelector, { timeout: 8000 }).catch(() => null);

      if (helpBtn) {
        await helpBtn.click();
        await sleep(1000);

        const earnItem = await page.evaluateHandle(() => {
          const items = Array.from(document.querySelectorAll('[data-test="dropdown-item"], .dropdown-item, [role="menuitem"]'));
          return items.find((el) => el.innerText && el.innerText.includes('Earn free credits')) || null;
        });

        if (earnItem && earnItem.asElement()) {
          await earnItem.asElement().click();
          await sleep(2000);

          const modalSection = await page.waitForSelector('[data-test="modal-section"], .Modal', { timeout: 6000 }).catch(() => null);
          if (modalSection) {
            report('info', 'Claiming $1 bonus rewards...');
            for (let claimIdx = 0; claimIdx < 5; claimIdx++) {
              try {
                const getOneBtns = await page.$$('button');
                let clicked = false;
                for (const b of getOneBtns) {
                  const txt = await page.evaluate((el) => el.innerText, b);
                  if (txt && txt.trim() === 'Get $1') {
                    await b.click();
                    clicked = true;
                    await sleep(2000);

                    // Close any newly opened popup tabs
                    const pages = await browser.pages();
                    if (pages.length > 2) {
                      for (let pi = pages.length - 1; pi >= 2; pi--) {
                        try { await pages[pi].close(); } catch {}
                      }
                    }
                    break;
                  }
                }
                if (!clicked) break;
              } catch {}
            }

            const closeBtn = await page.$('[data-test="modal-close-button"], button.Modal-header-close');
            if (closeBtn) await closeBtn.click();
          }
        }
      }
    } catch (e) {
      report('info', `Bonus credit step skipped: ${e.message}`);
    }

    // Step 7: Extract API Token from Settings -> Integrations
    logger.step(6, 6, 'Navigating to API & Integrations to extract Personal API Token...');
    report('info', 'Extracting Personal API Token from settings/integrations...');
    await page.goto('https://console.apify.com/settings/integrations', {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });
    await sleep(3000);

    let apiToken = null;
    let userData = null;

    // 1. Try clicking visibility toggle button to reveal token, then copy from code block
    try {
      const toggleVisBtn = await page.waitForSelector('button[data-test="toggle-visibility-button"]', { timeout: 8000 }).catch(() => null);
      if (toggleVisBtn) {
        await toggleVisBtn.click();
        await sleep(1000);
      }

      const tokenContainer = await page.$('[data-test="integrations-token-list-item-token"], [data-test="code-block-code"]');
      if (tokenContainer) {
        const text = await page.evaluate((el) => el.innerText || el.textContent, tokenContainer);
        const match = text?.match(/apify_api_[a-zA-Z0-9]{32,}/);
        if (match) {
          apiToken = match[0];
          report('success', `Extracted API Token: ${apiToken.slice(0, 12)}...`);
        }
      }
    } catch (e) {
      report('warn', `Could not extract token via visibility toggle: ${e.message}`);
    }

    // 2. Fallback: Internal API query using active session cookies
    if (!apiToken) {
      try {
        const userRes = await page.evaluate(async () => {
          const res = await fetch('https://api.apify.com/v2/users/me', { credentials: 'include' });
          if (!res.ok) return null;
          return res.json();
        });

        if (userRes?.data?.token) {
          apiToken = userRes.data.token;
          userData = userRes.data;
          report('success', `Retrieved token via session API: ${apiToken.slice(0, 12)}...`);
        }
      } catch (e) {}
    }

    // 3. Fallback: Search all token inputs or entire page text
    if (!apiToken) {
      const pageText = await page.evaluate(() => document.body.innerText);
      const match = pageText.match(/apify_api_[a-zA-Z0-9]{32,}/);
      if (match) {
        apiToken = match[0];
        report('success', `Extracted token from page text: ${apiToken.slice(0, 12)}...`);
      }
    }

    if (!apiToken) {
      throw new Error('Account created and verified, but failed to extract Personal API Token automatically.');
    }

    // Save account info to SQLite database
    try {
      const dbSaved = saveAccount({
        email,
        password,
        token: apiToken,
        userId: userData?.id || null,
        username: userData?.username || null,
        status: 'active',
      });
      report('success', `Account #${dbSaved.id} (${email}) saved to SQLite database!`);
    } catch (e) {
      report('warn', `Failed to save account to SQLite database: ${e.message}`);
    }

    // Save to accounts.json
    try {
      let existing = [];
      try {
        const raw = await fs.readFile(accountsFile, 'utf8');
        existing = JSON.parse(raw);
        if (!Array.isArray(existing)) existing = [];
      } catch {}
      existing.unshift({
        email,
        password,
        token: apiToken,
        userId: userData?.id || null,
        username: userData?.username || null,
        createdAt: new Date().toISOString(),
      });
      await fs.writeFile(accountsFile, JSON.stringify(existing, null, 2), 'utf8');
      report('info', `Credentials saved to ${accountsFile}`);
    } catch (e) {
      report('warn', `Failed to write accounts.json: ${e.message}`);
    }

    // Save to .env if enabled
    if (saveToEnv) {
      await updateEnvFile(apiToken);
    }

    logger.banner(`ACCOUNT READY!\nEmail: ${email}\nToken: ${apiToken}`);

    return {
      success: true,
      email,
      password,
      token: apiToken,
      user: userData,
    };
  } finally {
    await browser.close();
  }
}

/**
 * Updates .env with APIFY_TOKEN
 */
async function updateEnvFile(token) {
  const envPath = path.resolve(process.cwd(), '.env');
  try {
    let content = '';
    try {
      content = await fs.readFile(envPath, 'utf8');
    } catch {}

    if (/APIFY_TOKEN=.*/.test(content)) {
      content = content.replace(/APIFY_TOKEN=.*/, `APIFY_TOKEN=${token}`);
    } else {
      content = content ? `${content.trim()}\nAPIFY_TOKEN=${token}\n` : `APIFY_TOKEN=${token}\n`;
    }

    await fs.writeFile(envPath, content, 'utf8');
    logger.success(`Updated .env with APIFY_TOKEN=${token.slice(0, 12)}...`);
  } catch (err) {
    logger.warn(`Could not update .env automatically: ${err.message}`);
  }
}

// Standalone CLI execution
if (process.argv[1] && process.argv[1].endsWith('accountCreator.js')) {
  try {
    const isHeadless = process.argv.includes('--headless');
    await createApifyAccount({ headless: isHeadless });
  } catch (err) {
    logger.error(`Account creation failed: ${err.message}`);
    process.exit(1);
  }
}
