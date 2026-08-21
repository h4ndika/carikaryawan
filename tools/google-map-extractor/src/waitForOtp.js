import { getLatestApifyVerifyLink } from './getOtp.js';

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * Poll Gmail API until Apify verification link arrives
 *
 * @param {Object} gmail
 * @param {string} recipientEmail
 * @param {Object} [options]
 * @param {number} [options.timeoutSeconds=120]
 * @param {number} [options.intervalSeconds=3]
 * @returns {Promise<string>} Verification URL
 */
export async function waitForApifyEmail(gmail, recipientEmail, {
  timeoutSeconds = 120,
  intervalSeconds = 3,
} = {}) {
  const start = Date.now();

  while ((Date.now() - start) / 1000 < timeoutSeconds) {
    const link = await getLatestApifyVerifyLink(gmail, {
      senderEmail: 'hello@apify.com',
      recipientEmail,
      maxAgeSeconds: 180,
    });

    if (link) {
      return link;
    }

    await sleep(intervalSeconds * 1000);
  }

  throw new Error(`Timed out waiting for Apify verification email for ${recipientEmail} (${timeoutSeconds}s)`);
}
