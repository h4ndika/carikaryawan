import { google } from 'googleapis';
import { authorize } from './auth.js';

function base64UrlDecode(data) {
  if (!data) return '';
  return Buffer.from(data, 'base64').toString('utf-8');
}

function extractBody(payload) {
  let body = '';
  if (!payload) return '';

  if (payload.parts) {
    for (const part of payload.parts) {
      if (part.mimeType === 'text/plain' || part.mimeType === 'text/html') {
        if (part.body?.data) {
          body += base64UrlDecode(part.body.data);
        }
      } else if (part.parts) {
        body += extractBody(part);
      }
    }
  } else if (payload.body?.data) {
    body += base64UrlDecode(payload.body.data);
  }

  return body;
}

/**
 * Extract OTP code or link from email message body (Multi-platform & GitHub aware)
 */
export async function getLatestOtp(gmail, senderEmail, recipientEmail = null, subject = null, maxAgeSeconds = 180) {
  let query = `from:${senderEmail} newer_than:1d`;
  if (recipientEmail) {
    query += ` to:${recipientEmail}`;
  }
  if (subject) {
    query += ` subject:"${subject}"`;
  }

  const res = await gmail.users.messages.list({
    userId: 'me',
    q: query,
    maxResults: 5,
  });

  const messages = res.data.messages;
  if (!messages || messages.length === 0) {
    return null;
  }

  for (const msg of messages) {
    const message = await gmail.users.messages.get({
      userId: 'me',
      id: msg.id,
      format: 'full',
    });

    const headers = message.data.payload?.headers || [];

    // Verify recipient
    if (recipientEmail) {
      const toHeader = headers.find((h) => h.name.toLowerCase() === 'to');
      if (!toHeader || !toHeader.value.toLowerCase().includes(recipientEmail.toLowerCase())) {
        continue;
      }
    }

    // Verify subject
    if (subject) {
      const subjectHeader = headers.find((h) => h.name.toLowerCase() === 'subject');
      if (!subjectHeader || !subjectHeader.value.toLowerCase().includes(subject.toLowerCase())) {
        continue;
      }
    }

    // Check age
    const internalDate = parseInt(message.data.internalDate, 10) / 1000;
    const now = Date.now() / 1000;
    if (now - internalDate > maxAgeSeconds) {
      continue;
    }

    // Mark as read
    try {
      await gmail.users.messages.modify({
        userId: 'me',
        id: msg.id,
        requestBody: { removeLabelIds: ['UNREAD'] },
      });
    } catch {}

    const body = extractBody(message.data.payload);

    // ── 1. GitHub Launch code (8 digits) ──────────────────────────
    const ghLaunchHtml = body.match(/<span[^>]*font-size:\s*40px[^>]*>\s*(\d{8})\s*<\/span>/i);
    if (ghLaunchHtml) return ghLaunchHtml[1];

    const ghLaunchText = body.match(/launch code[\s\S]*?(\d{8})/i) || body.match(/entering the code below[\s\S]*?(\d{8})/i);
    if (ghLaunchText) return ghLaunchText[1];

    // ── 2. GitHub Device verification code (6 digits) ────────────
    const ghDeviceMatch = body.match(/verification\s+code:?\s*(\d{6})/i) || body.match(/verification code is:?\s*(\d{6})/i);
    if (ghDeviceMatch) return ghDeviceMatch[1];

    // ── 3. Generic 6-8 digit OTP ─────────────────────────────────
    const genericMatch = body.match(/(?<![#\dA-Fa-f])(\d{6,8})(?![\dA-Fa-f])/);
    if (genericMatch) return genericMatch[1];

    // ── 4. Alphanumeric OTP (e.g. 926-OQT) ───────────────────────
    const alphaMatch = body.match(/\b([A-Z0-9]{3}-[A-Z0-9]{3})\b/i);
    if (alphaMatch) return alphaMatch[1];
  }

  return null;
}

/**
 * Searches for latest Apify verification email using Gmail API
 */
export async function getLatestApifyVerifyLink(gmail, {
  senderEmail = 'hello@apify.com',
  recipientEmail = null,
  subject = null,
  maxAgeSeconds = 180,
} = {}) {
  let query = `from:${senderEmail} newer_than:1d`;
  if (recipientEmail) {
    query += ` to:${recipientEmail}`;
  }
  if (subject) {
    query += ` subject:"${subject}"`;
  }

  const res = await gmail.users.messages.list({
    userId: 'me',
    q: query,
    maxResults: 5,
  });

  const messages = res.data.messages;
  if (!messages || messages.length === 0) {
    return null;
  }

  for (const msg of messages) {
    const message = await gmail.users.messages.get({
      userId: 'me',
      id: msg.id,
      format: 'full',
    });

    const headers = message.data.payload?.headers || [];

    if (recipientEmail) {
      const toHeader = headers.find((h) => h.name.toLowerCase() === 'to');
      if (!toHeader || !toHeader.value.toLowerCase().includes(recipientEmail.toLowerCase())) {
        continue;
      }
    }

    if (subject) {
      const subjectHeader = headers.find((h) => h.name.toLowerCase() === 'subject');
      if (!subjectHeader || !subjectHeader.value.toLowerCase().includes(subject.toLowerCase())) {
        continue;
      }
    }

    const internalDate = parseInt(message.data.internalDate, 10) / 1000;
    const now = Date.now() / 1000;
    if (now - internalDate > maxAgeSeconds) {
      continue;
    }

    try {
      await gmail.users.messages.modify({
        userId: 'me',
        id: msg.id,
        requestBody: { removeLabelIds: ['UNREAD'] },
      });
    } catch {}

    const body = extractBody(message.data.payload);

    const match = body.match(/https:\/\/console\.apify\.com\/verify-email\/[a-zA-Z0-9_-]+/i) ||
                  body.match(/https:\/\/console\.apify\.com\/verify-email\?[^\s"'<>]+/i) ||
                  body.match(/https:\/\/console\.apify\.com\/api\/v1\/verify-email\?[^\s"'<>]+/i);

    if (match) {
      return match[0].replace(/&amp;/g, '&');
    }
  }

  return null;
}
