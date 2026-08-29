// Photo Scraper Service - sama persis dengan google-map-extractor/photoScraper.js
import { ApifyClient } from 'apify-client';

const DDG_IMAGE_SEARCH_URL = 'https://duckduckgo.com/i.js';
const APIFY_PHOTO_ACTOR = 'thescrappa/google-maps-photos-scraper';
const APIFY_PHOTO_ACTOR_ID = 'tLcUdNvmqkibhufg4';

/**
 * Scrape place photos with multi-tier fallback
 * Priority: 1. Apify Google Maps Photos -> 2. DuckDuckGo API -> 3. Bing Images HTML
 */
export async function scrapePlacePhotos(query, limit = 8, options = {}) {
  if (!query || typeof query !== 'string' || !query.trim()) return [];

  const cleanQuery = query.trim().replace(/\s+/g, ' ');
  const images = new Set();

  // ── Strategy 1: Apify Actor (thescrappa/google-maps-photos-scraper) - Priority 1 ──
  const placeId = options.placeId;
  if (placeId && typeof placeId === 'string' && placeId.startsWith('ChI')) {
    try {
      const token = options.apifyToken || process.env.APIFY_TOKEN;
      if (token) {
        const client = new ApifyClient({ token });
        
        console.log(`Apify (thescrappa) scraping for place_id: ${placeId}`);
        
        // thescrappa actor expects searchQuery and placeId
        const actorInput = {
          searchQuery: '', // Empty since we have placeId
          placeId: placeId,
          maxImages: limit || 8,
          includeStreetView: false, // Exclude street view photos
        };

        const run = await client.actor(APIFY_PHOTO_ACTOR_ID).call(actorInput, {
          memory: 2048,
          timeout: 90,
        });

        if (run.status === 'SUCCEEDED') {
          const dataset = await client.dataset(run.defaultDatasetId).listItems({ clean: true });
          const items = dataset.items || [];
          
          for (const item of items) {
            // Handle both photo_url_large and photo_url fields
            const photoUrl = item.photo_url_large || item.photo_url;
            if (photoUrl && isValidImageUrl(photoUrl)) {
              images.add(photoUrl);
              if (images.size >= limit) {
                console.log(`Apify success: ${images.size} photos`);
                return Array.from(images);
              }
            }
          }
          console.log(`Apify returned ${images.size} photos`);
        } else {
          console.log(`Apify status: ${run.status}`);
        }
      }
    } catch (apifyErr) {
      console.log('Apify error, fallback to DuckDuckGo:', apifyErr.message);
    }
  }

  // ── Strategy 2: DuckDuckGo Images API (Priority 2) ───────────
  if (images.size < 3) {
    try {
      const vqd = await getDuckDuckGoVQD(cleanQuery);
      if (vqd) {
        const params = new URLSearchParams({
          l: 'id-id',
          o: 'json',
          q: cleanQuery,
          vqd: vqd,
          f: ',,,',
          p: '1',
        });

        const res = await fetch(`${DDG_IMAGE_SEARCH_URL}?${params.toString()}`, {
          headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept': 'application/json',
            'Referer': 'https://duckduckgo.com/',
          },
        });

        if (res.ok) {
          const data = await res.json();
          const results = data.results || [];
          for (const item of results) {
            const imgUrl = item.image || item.thumbnail;
            if (imgUrl && isValidImageUrl(imgUrl)) {
              images.add(imgUrl);
              if (images.size >= limit) break;
            }
          }
          console.log(`DuckDuckGo returned ${images.size} total photos`);
        }
      }
    } catch (err) {
      console.log('DuckDuckGo fallback to Bing');
    }
  }

  // ── Strategy 3: Bing Images HTML Scraping (Priority 3) ───────
  if (images.size < 3) {
    try {
      const bingUrl = `https://www.bing.com/images/search?q=${encodeURIComponent(cleanQuery)}&form=HDRSC2&first=1`;
      const res = await fetch(bingUrl, {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
          'Accept-Language': 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        },
      });

      if (res.ok) {
        const html = await res.text();
        const murlMatches = html.match(/murl&quot;:&quot;(https?:\/\/[^&"]+)&quot;/g) || [];
        for (const match of murlMatches) {
          const urlMatch = match.match(/https?:\/\/[^&"]+/);
          if (urlMatch && isValidImageUrl(urlMatch[0])) {
            images.add(urlMatch[0]);
            if (images.size >= limit) break;
          }
        }
        console.log(`Bing returned ${images.size} total photos`);
      }
    } catch (err) {
      console.log('Bing scrape error:', err.message);
    }
  }

  return Array.from(images);
}

/**
 * Obtain VQD token required for DuckDuckGo image searches
 */
async function getDuckDuckGoVQD(query) {
  try {
    const url = `https://duckduckgo.com/?q=${encodeURIComponent(query)}&iax=images&ia=images`;
    const res = await fetch(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
      },
    });
    if (!res.ok) return null;
    const text = await res.text();
    const vqdMatch = text.match(/vqd=["']?([^"'\s&]+)/i);
    return vqdMatch ? vqdMatch[1] : null;
  } catch {
    return null;
  }
}

function isValidImageUrl(url) {
  if (!url || typeof url !== 'string') return false;
  if (url.startsWith('data:')) return false;
  if (url.includes('favicon') || url.includes('logo') || url.includes('icon')) return false;
  return /^https?:\/\//i.test(url);
}
