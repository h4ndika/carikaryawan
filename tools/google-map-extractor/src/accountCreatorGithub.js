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
import { getLatestOtp } from './getOtp.js';
import { findSystemBrowserExecutable } from './accountCreator.js';

// Apply stealth plugin
puppeteer.use(StealthPlugin());

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/**
 * Read and parse GitHub accounts from v2/src/github_accounts.txt
 * Format: email:username:password
 */
export async function loadGithubAccounts() {
  const filePath = path.resolve(process.cwd(), 'src', 'github_accounts.txt');
  if (!fsSync.existsSync(filePath)) {
    throw new Error(`File github_accounts.txt not found at: ${filePath}`);
  }

  const raw = await fs.readFile(filePath, 'utf8');
  const lines = raw.split('\n').map((l) => l.trim()).filter((l) => l && !l.startsWith('#'));

  const accounts = [];
  for (const line of lines) {
    const parts = line.split(':');
    if (parts.length >= 3) {
      accounts.push({
        email: parts[0].trim(),
        username: parts[1].trim(),
        password: parts.slice(2).join(':').trim(),
      });
    }
  }

  return accounts;
}

/**
 * Poll Gmail API for GitHub device verification code
 */
async function waitForGithubOtp(gmail, recipientEmail, timeoutSeconds = 120) {
  logger.info(`Polling Gmail API for GitHub OTP sent to ${recipientEmail}...`);
  const start = Date.now();

  while ((Date.now() - start) / 1000 < timeoutSeconds) {
    const otp = await getLatestOtp(gmail, 'noreply@github.com', recipientEmail, null, 180);
    if (otp) {
      return otp;
    }
    await sleep(4000);
  }

  throw new Error(`Timeout waiting for GitHub verification code for ${recipientEmail}`);
}

/**
 * Automates Apify Account Creation using GitHub OAuth
 *
 * @param {Object} [options={}]
 * @param {Object} [options.account] - Specific { email, username, password }
 * @param {boolean} [options.headless=false]
 * @param {boolean} [options.saveToEnv=true]
 * @param {Function} [options.onProgress]
 */
export async function createApifyAccountWithGithub(options = {}) {
  const headless = options.headless ?? false;
  const saveToEnv = options.saveToEnv ?? true;
  const accountsFile = path.resolve(process.cwd(), 'accounts.json');

  const report = (level, message, data = null) => {
    if (level === 'error') logger.error(message);
    else if (level === 'success') logger.success(message);
    else if (level === 'warn') logger.warn(message);
    else logger.info(message);

    if (typeof options.onProgress === 'function') {
      try {
        options.onProgress({ level, message, data, timestamp: new Date().toISOString() });
      } catch { }
    }
  };

  logger.banner('Apify Account Creator (GitHub OAuth Edition)');

  // 1. Authorize Gmail API
  logger.step(1, 7, 'Connecting to Gmail API for GitHub OTPs...');
  report('info', 'Authorizing Gmail API via credentials.json & token.json...');
  const auth = await authorize();
  const gmail = google.gmail({ version: 'v1', auth });
  report('success', 'Gmail API connected.');

  // 2. Select GitHub account
  let targetAccount = options.account;
  if (!targetAccount) {
    const allGithub = await loadGithubAccounts();
    if (!allGithub.length) {
      throw new Error('No accounts found in github_accounts.txt!');
    }
    targetAccount = allGithub[Math.floor(Math.random() * allGithub.length)];
  }

  logger.step(2, 7, `Selected GitHub account: ${targetAccount.username} (${targetAccount.email})`);
  report('info', `Target GitHub: ${targetAccount.username} | Email: ${targetAccount.email}`);

  // 3. Launch browser
  logger.step(3, 7, 'Launching Puppeteer browser...');
  const executablePath = findSystemBrowserExecutable();
  if (executablePath) {
    logger.info(`Using browser: ${executablePath}`);
  }

  const browser = await puppeteer.launch({
    headless: headless ? 'new' : false,
    executablePath,
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
  });

  try {
    const page = await browser.newPage();
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'en-US,en;q=0.9' });

    // 4. Navigate to Apify Sign Up & Click Continue with GitHub
    logger.step(4, 7, 'Navigating to Apify sign-up and clicking GitHub OAuth...');
    report('info', 'Opening https://console.apify.com/sign-up...');
    await page.goto('https://console.apify.com/sign-up', {
      waitUntil: 'networkidle2',
      timeout: 60000,
    });
    await sleep(2500);

    // Explicitly target ONLY data-test="github-oauth-button" or button containing "GitHub"
    const githubBtnSelector = 'button[data-test="github-oauth-button"]';
    report('info', 'Finding "Continue with GitHub" button via data-test="github-oauth-button"...');

    let clickedGithub = false;
    try {
      await page.waitForSelector(githubBtnSelector, { visible: true, timeout: 15000 });
      await page.click(githubBtnSelector);
      clickedGithub = true;
    } catch {
      // Fallback: search by innerText to strictly avoid Google/Gmail button
      const clicked = await page.evaluate(() => {
        const btns = Array.from(document.querySelectorAll('button'));
        const gh = btns.find((b) => b.getAttribute('data-test') === 'github-oauth-button' || (b.innerText && b.innerText.includes('GitHub')));
        if (gh) {
          gh.click();
          return true;
        }
        return false;
      });
      clickedGithub = clicked;
    }

    if (!clickedGithub) {
      throw new Error('Could not find or click "Continue with GitHub" button on Apify sign-up page.');
    }

    report('success', 'Clicked "Continue with GitHub" successfully.');
    await sleep(3500);

    // 5. GitHub Login & Device Verification Flow
    logger.step(5, 7, 'Logging in to GitHub & handling device verification...');
    report('info', `Current URL: ${page.url()}`);

    // If redirected to GitHub login
    if (page.url().includes('github.com/login') || page.url().includes('github.com/session')) {
      report('info', `Entering GitHub credentials for ${targetAccount.username}...`);
      await page.waitForSelector('#login_field', { timeout: 15000 });
      await page.type('#login_field', targetAccount.username || targetAccount.email, { delay: 40 });
      await sleep(500);

      await page.waitForSelector('#password', { timeout: 15000 });
      await page.type('#password', targetAccount.password, { delay: 40 });
      await sleep(500);

      report('info', 'Submitting GitHub sign in...');
      await page.click('input[type="submit"], button[type="submit"]');
      await sleep(4000);
    }

    // Check for GitHub Device Verification / 2FA
    let needsOtp = false;
    try {
      needsOtp = page.url().includes('verified-device') || page.url().includes('two-factor') || (await page.$('#otp, input[name="otp"], #launch-code-0')) !== null;
    } catch {}

    if (needsOtp) {
      report('info', `GitHub device verification required! Fetching OTP for ${targetAccount.email}...`);
      const otp = await waitForGithubOtp(gmail, targetAccount.email, 120);
      report('success', `GitHub OTP received: ${otp}`);

      const digits = otp.replace(/\D/g, '');

      try {
        const hasLaunchCode = await page.$('#launch-code-0');
        if (hasLaunchCode) {
          for (let i = 0; i < Math.min(digits.length, 8); i++) {
            const inputSel = `#launch-code-${i}`;
            try {
              await page.type(inputSel, digits[i], { delay: 40 });
            } catch {}
          }
        } else {
          const otpInput = await page.$('#otp, input[name="otp"], input[autocomplete="one-time-code"]');
          if (otpInput) {
            await page.type('#otp, input[name="otp"], input[autocomplete="one-time-code"]', digits, { delay: 50 });
            await sleep(500);
            try {
              const submitOtp = await page.$('button[type="submit"], input[type="submit"]');
              if (submitOtp) await submitOtp.click();
            } catch {}
          }
        }
      } catch (err) {
        // Ignored: auto-submit during typing destroys context, which indicates successful navigation
        report('info', 'OTP submitted, waiting for navigation...');
      }

      // Wait safely for navigation away from verification page
      await sleep(5000);
    }

    // Check for GitHub OAuth Authorize page (Strict human interaction & focus)
    report('info', 'Waiting for GitHub Authorize page or Apify redirect...');
    for (let check = 0; check < 20; check++) {
      try {
        const currentUrl = page.url();
        if (currentUrl.includes('apify.com') && !currentUrl.includes('github.com')) {
          report('info', 'Redirected to Apify console.');
          break;
        }

        // Focus browser page to activate strict GitHub button
        try { await page.bringToFront(); } catch {}
        
        // Find Authorize button using exact multi-selectors from 9router scripts
        const authSelectors = [
          'button[data-octo-click="oauth_application_authorization"]',
          'button.js-oauth-authorize-btn',
          'button[name="authorize"]',
          'button#js-oauth-authorize-btn',
        ];

        let authBtn = null;
        for (const sel of authSelectors) {
          try {
            authBtn = await page.$(sel);
            if (authBtn) break;
          } catch {}
        }

        if (!authBtn) {
          try {
            authBtn = await page.waitForSelector('::-p-xpath(//button[contains(., "Authorize")])', { timeout: 1000 });
          } catch {}
        }

        if (authBtn) {
          report('info', 'GitHub Authorize button detected. Focusing and clicking...');

          // Move mouse randomly over the page and over the button to trigger GitHub focus event
          try {
            await page.mouse.move(200, 300);
            await sleep(300);
            const box = await authBtn.boundingBox();
            if (box) {
              await page.mouse.move(box.x + box.width / 2, box.y + box.height / 2);
              await sleep(400);
            }
          } catch {}

          // Wait until button is not disabled
          await page.waitForFunction(
            (btn) => !btn.disabled && btn.getAttribute('aria-disabled') !== 'true',
            { timeout: 10000, polling: 300 },
            authBtn
          ).catch(() => {});

          await sleep(600);

          // Scroll into view & click via evaluate + fallback click
          await page.evaluate((btn) => {
            btn.scrollIntoView({ block: 'center' });
            btn.focus();
            btn.click();
          }, authBtn);

          try { await authBtn.click(); } catch {}

          report('success', 'Clicked GitHub Authorize button.');
          await sleep(4000);
          break;
        }
      } catch (e) {}
      await sleep(1500);
    }

    // 6. Handle "Welcome to Apify" Onboarding Form
    logger.step(6, 7, 'Handling "Welcome to Apify" onboarding form...');
    await sleep(3000);

    try {
      const nameInputSelector = 'input[data-test="name-input"], input#name, input[name="name"]';
      const nameInput = await page.waitForSelector(nameInputSelector, { timeout: 12000 });
      if (nameInput) {
        const randomName = targetAccount.username || `User ${Math.random().toString(36).substring(2, 7)}`;
        report('info', `Entering onboarding full name: "${randomName}"...`);
        await page.type(nameInputSelector, randomName, { delay: 40 });
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
        ).catch(() => { });

        await page.click(continueBtnSelector);
        report('info', 'Submitted onboarding form.');
        await sleep(3000);
      }
    } catch {
      report('info', 'Onboarding name form bypassed or not required.');
    }

    // 7. Claim $5 Free Credits (Gamification Modals)
    try {
      report('info', 'Checking for $5 free credits reward in Help & Resources...');
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
            report('info', 'Gamification modal opened. Claiming all 5 bonus rewards ($1 each)...');

            for (let claimIdx = 0; claimIdx < 5; claimIdx++) {
              try {
                // Focus Apify main tab
                try { await page.bringToFront(); } catch {}

                // Wait until at least one button is enabled with exact text "Get $1" (not "Claiming..." or disabled)
                await page.waitForFunction(
                  () => {
                    const btns = Array.from(document.querySelectorAll('button'));
                    return btns.some((b) => (b.innerText || '').trim() === 'Get $1' && !b.disabled);
                  },
                  { timeout: 25000, polling: 500 }
                ).catch(() => {});

                // Find the first available and enabled "Get $1" button
                const targetBtnHandle = await page.evaluateHandle(() => {
                  const btns = Array.from(document.querySelectorAll('button'));
                  return btns.find((b) => (b.innerText || '').trim() === 'Get $1' && !b.disabled) || null;
                });

                if (targetBtnHandle && targetBtnHandle.asElement()) {
                  report('info', `Claiming reward #${claimIdx + 1}/5: Clicking "Get $1"...`);
                  await targetBtnHandle.asElement().click();
                  await sleep(2000);

                  // Close newly opened target=_blank tab immediately
                  const allPages = await browser.pages();
                  if (allPages.length > 2) {
                    for (let pi = allPages.length - 1; pi >= 2; pi--) {
                      try { await allPages[pi].close(); } catch {}
                    }
                  }

                  // Switch back to Apify tab and wait for "Claiming..." state to finish (~6-10s)
                  try { await page.bringToFront(); } catch {}
                  report('info', `Reward #${claimIdx + 1} processing (waiting for button state to settle)...`);

                  // Wait until "Claiming..." spinner/state finishes
                  await sleep(8000);
                } else {
                  report('info', `No more unclaimed "Get $1" buttons found after step #${claimIdx}.`);
                  break;
                }
              } catch (claimErr) {
                report('warn', `Claim step #${claimIdx + 1} notice: ${claimErr.message}`);
                await sleep(3000);
              }
            }

            // Close gamification modal after claiming
            try {
              const closeBtn = await page.$('[data-test="modal-close-button"], button.Modal-header-close');
              if (closeBtn) {
                await closeBtn.click();
                await sleep(1000);
              }
            } catch {}
          }
        }
      }
    } catch (e) {
      report('info', `Bonus credit step skipped: ${e.message}`);
    }

    // 8. Extract Personal API Token from Settings -> Integrations
    logger.step(7, 7, 'Extracting Personal API Token from settings/integrations...');
    report('info', 'Opening https://console.apify.com/settings/integrations...');
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
        report('info', 'Revealing API Token in UI...');
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
      } catch (e) { }
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

    // 9. Save account info to SQLite database
    try {
      const dbSaved = saveAccount({
        email: targetAccount.email,
        password: targetAccount.password,
        token: apiToken,
        userId: userData?.id || null,
        username: targetAccount.username || userData?.username || null,
        status: 'active',
      });
      report('success', `Account #${dbSaved.id} (${targetAccount.email}) saved to SQLite database!`);
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
      } catch { }
      existing.unshift({
        email: targetAccount.email,
        username: targetAccount.username,
        password: targetAccount.password,
        token: apiToken,
        userId: userData?.id || null,
        method: 'github_oauth',
        createdAt: new Date().toISOString(),
      });
      await fs.writeFile(accountsFile, JSON.stringify(existing, null, 2), 'utf8');
      report('info', `Credentials saved to ${accountsFile}`);
    } catch (e) {
      report('warn', `Failed to write accounts.json: ${e.message}`);
    }

    // Save to .env if enabled
    if (saveToEnv) {
      const envPath = path.resolve(process.cwd(), '.env');
      try {
        let content = '';
        try { content = await fs.readFile(envPath, 'utf8'); } catch { }
        if (/APIFY_TOKEN=.*/.test(content)) {
          content = content.replace(/APIFY_TOKEN=.*/, `APIFY_TOKEN=${apiToken}`);
        } else {
          content = content ? `${content.trim()}\nAPIFY_TOKEN=${apiToken}\n` : `APIFY_TOKEN=${apiToken}\n`;
        }
        await fs.writeFile(envPath, content, 'utf8');
        report('info', `Updated .env with APIFY_TOKEN=${apiToken.slice(0, 12)}...`);
      } catch { }
    }

    logger.banner(`APIFY GITHUB ACCOUNT READY!\nEmail: ${targetAccount.email}\nUsername: ${targetAccount.username}\nToken: ${apiToken}`);

    return {
      success: true,
      email: targetAccount.email,
      username: targetAccount.username,
      password: targetAccount.password,
      token: apiToken,
      user: userData,
    };
  } finally {
    await browser.close();
  }
}

// Standalone CLI execution
if (process.argv[1] && process.argv[1].endsWith('accountCreatorGithub.js')) {
  try {
    const isHeadless = process.argv.includes('--headless');
    await createApifyAccountWithGithub({ headless: isHeadless });
  } catch (err) {
    logger.error(`GitHub Account creation failed: ${err.message}`);
    process.exit(1);
  }
}
