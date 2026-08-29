<?php

namespace App\Services;

use App\Models\ApifyAccount;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhotoScraperService
{
    protected const DDG_IMAGE_SEARCH_URL = 'https://duckduckgo.com/i.js';
    protected const APIFY_PHOTO_ACTOR = 'solidcode/google-maps-photos-scraper';

    /**
     * Scrape place photos using sequential multi-tier fallback identical to tools/google-map-extractor:
     * Priority 1: DuckDuckGo Images API
     * Priority 2: Bing Images HTML Scraping (if < 3 images)
     * Priority 3: Apify Actor Fallback (if 0 images & placeId is valid ChIJ...)
     *
     * @param string $query (e.g. "Hotel Tarzan Marriott On Penida Bali")
     * @param int $limit Max photos to return
     * @param array $options ['place_id' => '...', 'apify_token' => '...', 'existing_photos' => []]
     * @return array List of valid absolute image URLs
     */
    public function scrapePhotos(string $query, int $limit = 8, array $options = []): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query));
        if (empty($query)) {
            return $options['existing_photos'] ?? [];
        }

        $images = $options['existing_photos'] ?? [];

        // ── Strategy 1: DuckDuckGo Images API (Priority 1) ─────────────────
        try {
            $vqd = $this->getDuckDuckGoVQD($query);
            if ($vqd) {
                $ddgImages = $this->fetchDdgImagesWithVqd($query, $vqd, $limit);
                foreach ($ddgImages as $img) {
                    if (!in_array($img, $images)) {
                        $images[] = $img;
                        if (count($images) >= $limit) {
                            return array_slice(array_values(array_unique($images)), 0, $limit);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            Log::debug("DuckDuckGo photo scrape notice for '{$query}': " . $e->getMessage());
        }

        // ── Strategy 2: Bing Images HTML Scraping (Priority 2, if < 3 images) ──
        if (count($images) < 3) {
            try {
                $bingImages = $this->scrapeFromBing($query, $limit);
                foreach ($bingImages as $img) {
                    if (!in_array($img, $images)) {
                        $images[] = $img;
                        if (count($images) >= $limit) {
                            return array_slice(array_values(array_unique($images)), 0, $limit);
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::debug("Bing photo scrape notice for '{$query}': " . $e->getMessage());
            }
        }

        // ── Strategy 3: Apify Actor Fallback (solidcode/google-maps-photos-scraper) ──
        // Only called if search engines yielded 0 photos and placeId is present
        $placeId = $options['place_id'] ?? null;
        if (empty($images) && !empty($placeId) && str_starts_with($placeId, 'ChI')) {
            try {
                $token = $options['apify_token'] ?? null;
                if (!$token) {
                    $acc = ApifyAccount::getNextActiveAccount();
                    $token = $acc?->token ?? config('services.apify.token') ?? env('APIFY_TOKEN');
                }

                if ($token) {
                    $apifyImages = $this->scrapeFromApifyActor($placeId, $token, $limit);
                    foreach ($apifyImages as $img) {
                        if (!in_array($img, $images)) {
                            $images[] = $img;
                            if (count($images) >= $limit) {
                                break;
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::debug("Apify photo scrape notice for placeId '{$placeId}': " . $e->getMessage());
            }
        }

        return array_slice(array_values(array_unique($images)), 0, $limit);
    }

    /**
     * Scrape photos for a specific Post model and attach them to the post and post_images table.
     */
    public function scrapeAndAttachForPost(
        Post $post,
        int $limit = 8,
        bool $replaceExisting = false,
        bool $publishOnComplete = false
    ): array {
        // Build an accurate, descriptive location search query with category context
        $queryParts = [];

        // Add category prefix if not already present in the title
        if ($post->category?->name) {
            $catName = trim($post->category->name);
            if (!str_contains(strtolower($post->title), strtolower($catName))) {
                $queryParts[] = $catName;
            }
        }

        $queryParts[] = $post->title;

        if ($post->city?->name) {
            $queryParts[] = $post->city->name;
        }
        if ($post->province?->name && (!$post->city || !str_contains($post->city->name, $post->province->name))) {
            $queryParts[] = $post->province->name;
        }

        $query = implode(' ', $queryParts);

        // Keep existing images if not replacing
        $existingImages = [];
        if (!$replaceExisting) {
            $existingImages = $post->images()->pluck('image_url')->filter()->values()->all();
            if (!empty($post->thumbnail) && !in_array($post->thumbnail, $existingImages)) {
                $existingImages[] = $post->thumbnail;
            }
        }

        $options = [
            'place_id' => $post->place_id,
            'existing_photos' => $existingImages,
        ];

        $photos = $this->scrapePhotos($query, $limit, $options);

        if (empty($photos)) {
            return [
                'success' => false,
                'message' => "Tidak ditemukan foto baru untuk: {$post->title}",
                'count' => 0,
                'total' => count($existingImages),
            ];
        }

        if ($replaceExisting) {
            PostImage::where('post_id', $post->id)->delete();
            $existingCount = 0;
        } else {
            $existingCount = PostImage::where('post_id', $post->id)->count();
        }

        $insertedCount = 0;
        foreach ($photos as $url) {
            $exists = PostImage::where('post_id', $post->id)->where('image_url', $url)->exists();
            if ($exists) {
                continue;
            }

            PostImage::create([
                'post_id' => $post->id,
                'image_url' => $url,
                'caption' => $post->title,
                'order' => $existingCount + $insertedCount,
            ]);
            $insertedCount++;
        }

        $totalPhotosNow = PostImage::where('post_id', $post->id)->count();

        // Update post thumbnail if currently empty or if replaceExisting is enabled
        $updateData = [];
        if ((empty($post->thumbnail) || $replaceExisting) && !empty($photos[0])) {
            $updateData['thumbnail'] = $photos[0];
        }

        if ($publishOnComplete && $totalPhotosNow >= 2) {
            $updateData['status'] = 'published';
            if (empty($post->published_at)) {
                $updateData['published_at'] = now();
            }
        }

        if (!empty($updateData)) {
            $post->update($updateData);
        }

        return [
            'success' => $totalPhotosNow >= 2,
            'message' => $totalPhotosNow >= 2 
                ? "Berhasil menambahkan {$insertedCount} foto baru (Total: {$totalPhotosNow} foto)."
                : "Foto masih kurang (hanya {$totalPhotosNow} foto).",
            'count' => $insertedCount,
            'total' => $totalPhotosNow,
            'photos' => $photos,
        ];
    }

    /**
     * Obtain VQD token required for DuckDuckGo image searches
     */
    protected function getDuckDuckGoVQD(string $query): ?string
    {
        try {
            $url = 'https://duckduckgo.com/?q=' . urlencode($query) . '&iax=images&ia=images';
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            ])->timeout(10)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $body = $response->body();
            if (preg_match('/vqd=["\']?([^"\'\s&]+)/i', $body, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Fetch DDG images using VQD
     */
    protected function fetchDdgImagesWithVqd(string $query, string $vqd, int $limit): array
    {
        $response = Http::withoutVerifying()->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Referer' => 'https://duckduckgo.com/',
        ])->timeout(12)->get(self::DDG_IMAGE_SEARCH_URL, [
            'l' => 'id-id',
            'o' => 'json',
            'q' => $query,
            'vqd' => $vqd,
            'f' => ',,,',
            'p' => '1',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        $results = $data['results'] ?? [];
        $images = [];

        foreach ($results as $item) {
            $url = $item['image'] ?? $item['thumbnail'] ?? null;
            $title = $item['title'] ?? '';
            if ($url && $this->isValidImageUrl($url, $title)) {
                $images[] = $url;
                if (count($images) >= $limit) {
                    break;
                }
            }
        }

        return $images;
    }

    /**
     * Bing Images HTML scraper (Same as tools/google-map-extractor)
     */
    protected function scrapeFromBing(string $query, int $limit): array
    {
        $bingUrl = 'https://www.bing.com/images/search?q=' . urlencode($query) . '&form=HDRSC2&first=1';
        $response = Http::withoutVerifying()->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
        ])->timeout(12)->get($bingUrl);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();
        $images = [];

        // Match murl:"https://..."
        if (preg_match_all('/murl&quot;:&quot;(https?:\/\/[^&"]+)&quot;/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                if ($this->isValidImageUrl($url)) {
                    $images[] = $url;
                    if (count($images) >= $limit) {
                        break;
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Apify actor photo fallback (solidcode/google-maps-photos-scraper)
     */
    protected function scrapeFromApifyActor(string $placeId, string $token, int $limit): array
    {
        $cleanActor = str_replace('/', '~', self::APIFY_PHOTO_ACTOR);
        $url = "https://api.apify.com/v2/acts/{$cleanActor}/runs";

        $runRes = Http::withoutVerifying()->withToken($token)->timeout(25)->post($url, [
            'concurrentPlaces' => 1,
            'imageSize' => 'medium',
            'language' => 'id',
            'maxPhotosPerPlace' => $limit,
            'placeIds' => [$placeId],
        ]);

        if (!$runRes->successful()) {
            return [];
        }

        $datasetId = $runRes->json('data.defaultDatasetId');
        if (!$datasetId) {
            return [];
        }

        sleep(4);

        $dataRes = Http::withoutVerifying()->withToken($token)
            ->timeout(25)
            ->get("https://api.apify.com/v2/datasets/{$datasetId}/items?clean=true&limit={$limit}");

        if (!$dataRes->successful()) {
            return [];
        }

        $items = $dataRes->json() ?? [];
        $images = [];

        foreach ($items as $item) {
            $imgUrl = $item['imageUrl'] ?? $item['url'] ?? $item['image'] ?? $item['photoUrl'] ?? null;
            if ($imgUrl && $this->isValidImageUrl($imgUrl)) {
                $images[] = $imgUrl;
            }
        }

        return $images;
    }

    /**
     * Strict validation for photo URLs to filter out cartoons, clipart, icons, vectors, and wallpapers.
     */
    public function isValidImageUrl(?string $url, string $contextTitle = ''): bool
    {
        if (empty($url) || !is_string($url)) {
            return false;
        }

        if (str_starts_with($url, 'data:')) {
            return false;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $lower = strtolower($url . ' ' . $contextTitle);

        // Blacklisted keywords & domains for non-venue/cartoon/clipart/stock artifacts
        $blacklisted = [
            'favicon', 'logo', 'icon.', 'icon-', '.ico', '.svg', '.gif',
            'disney', 'cartoon', 'fandom.com', 'wikia.nocookie', 'anime',
            'clipart', 'vector', 'drawing', 'coloring-page', 'coloring_page',
            'pngwing', 'pngtree', 'freepik', 'cleanpng', 'stickers',
            'deviantart', 'fanart', 'pngimg', 'clipartkey', 'pinclipart',
            'wallpapercave', 'wallpaperflare', 'alphacoders', 'desktopnexus'
        ];

        foreach ($blacklisted as $bad) {
            if (str_contains($lower, $bad)) {
                return false;
            }
        }

        return true;
    }
}
