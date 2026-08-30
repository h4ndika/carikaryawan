// Photo Scraper Service - sama persis dengan google-map-extractor/photoScraper.js
import { ApifyClient } from 'apify-client';

const APIFY_PHOTO_ACTOR = 'lurkapi/google-maps-photos-and-videos-scraper';
const APIFY_PHOTO_ACTOR_ID = 'KpQF2B9mcQFkExt8X';

/**
 * Scrape place photos with Apify only
 * Priority 1: Apify Google Maps Photos (Only source)
 *
 * @param {string} query - Place name and location (unused, kept for compatibility)
 * @param {number} [limit=8] - Max photos to fetch
 * @param {Object} [options={}]
 * @param {string} [options.placeId] - Google Place ID (ChIJ...) for Apify
 * @param {string} [options.apifyToken] - Apify token override
 * @returns {Promise<string[]>} Array of image URLs
 */
export async function scrapePlacePhotos(query, limit = 8, options = {}) {
  if (!query || typeof query !== 'string' || !query.trim()) return [];

  const images = new Set();
  const placeId = options.placeId;

  // ── Apify Actor (lurkapi/google-maps-photos-and-videos-scraper) - Only source ──
  if (placeId && typeof placeId === 'string' && placeId.startsWith('ChI')) {
    try {
      const token = options.apifyToken || process.env.APIFY_TOKEN;
      if (token) {
        const client = new ApifyClient({ token });
        
        console.log(`Apify scraping for place_id: ${placeId}`);
        
        const actorInput = {
          categories: ['all'],
          imageSize: 'large',
          maxPhotosPerPlace: limit || 8,
          maxPlacesPerSearch: 1,
          outputDimensions: true,
          outputGps: true,
          outputInternalCategoryCode: false,
          outputPlaceIds: true,
          outputPlaceName: true,
          outputPlaceUrl: true,
          outputScrapedAt: true,
          outputSearchKeyword: true,
          outputSource: true,
          outputThumbnailUrl: true,
          outputUploadDate: true,
          outputUploadedByOwner: true,
          placeIds: [placeId],
        };

        const run = await client.actor(APIFY_PHOTO_ACTOR_ID).call(actorInput, {
          memory: 2048,
          timeout: 90,
        });

        if (run.status === 'SUCCEEDED') {
          const dataset = await client.dataset(run.defaultDatasetId).listItems({ clean: true });
          const items = dataset.items || [];
          
          for (const item of items) {
            const photoUrl = item.photoUrl || item.photo_url_large || item.photo_url || item.url || item.imageUrl || item.thumbnailUrl;
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
      console.log('Apify error:', apifyErr.message);
      return [];
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
