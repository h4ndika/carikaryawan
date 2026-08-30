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
     * Scrape place photos using Apify only (no unreliable search engines)
     * Priority 1: Apify Actor (Google Maps Places Photos) - Only source
     *
     * @param string $query (kept for compatibility, unused)
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

        // ── Apify Actor (Google Maps Photos) - Only source ──
        $placeId = $options['place_id'] ?? null;
        if (!empty($placeId) && str_starts_with($placeId, 'ChI')) {
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
                                return array_slice(array_values(array_unique($images)), 0, $limit);
                            }
                        }
                    }
                }
            } catch (Throwable $e) {
                Log::warning("Apify photo scrape failed for placeId '{$placeId}': " . $e->getMessage());
                return $options['existing_photos'] ?? [];
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
        // Normalize city & province name (strip KOTA, KAB., KABUPATEN, PROVINSI)
        $cleanCity = trim(preg_replace('/^(KOTA|KAB\.|KABUPATEN)\s+/i', '', $post->city?->name ?? ''));
        $cleanProv = trim(preg_replace('/^(PROV\.|PROVINSI)\s+/i', '', $post->province?->name ?? ''));

        $queryParts = [];

        // Add category prefix if not already present in the title
        if ($post->category?->name) {
            $catName = trim($post->category->name);
            if (!str_contains(strtolower($post->title), strtolower($catName))) {
                $queryParts[] = $catName;
            }
        }

        $queryParts[] = $post->title;

        if (!empty($cleanCity)) {
            $queryParts[] = $cleanCity;
        }

        if (!empty($cleanProv) && strtolower($cleanProv) !== strtolower($cleanCity)) {
            $queryParts[] = $cleanProv;
        }

        // Anchor query to Indonesia to avoid foreign keywords/markets
        $queryParts[] = 'Indonesia';

        $query = implode(' ', $queryParts);

        // Keep existing images if not replacing
        $existingImages = [];
        if (!$replaceExisting) {
            $existingImages = $post->images()->pluck('image_url')->filter(fn ($u) => $this->isValidImageUrl($u))->values()->all();
            if (!empty($post->thumbnail) && $this->isValidImageUrl($post->thumbnail) && !in_array($post->thumbnail, $existingImages)) {
                $existingImages[] = $post->thumbnail;
            }
        }

        $options = [
            'place_id' => $post->place_id,
            'target_title' => $post->title,
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
            // Delete previously saved invalid images if any
            foreach ($post->images as $existingImage) {
                if (!$this->isValidImageUrl($existingImage->image_url)) {
                    $existingImage->delete();
                }
            }
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

        // Always update post thumbnail with the first scraped photo
        $updateData = [];
        if (!empty($photos[0])) {
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
            $url = 'https://duckduckgo.com/?q=' . urlencode($query) . '&kl=id-id&kad=id_ID&iax=images&ia=images';
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
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
            'kl' => 'id-id',
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
     * Bing Images HTML scraper - simplified pattern matching tools/photoScraper.js
     */
    protected function scrapeFromBing(string $query, int $limit, string $targetTitle = ''): array
    {
        $bingUrl = 'https://www.bing.com/images/search?q=' . urlencode($query) . '&form=HDRSC2&first=1';
        
        // Match headers from successful Node.js implementation
        $response = Http::withoutVerifying()->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding' => 'gzip, deflate, br',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ])->timeout(12)->get($bingUrl);

        if (!$response->successful()) {
            return [];
        }

        $html = $response->body();
        $images = [];

        // Simple regex pattern matching tools implementation
        if (preg_match_all('/murl&quot;:&quot;(https?:\/\/[^&"]+)&quot;/i', $html, $matches)) {
            foreach ($matches[1] as $url) {
                // Basic validation only (like tools)
                if (!$this->isValidImageUrl($url)) {
                    continue;
                }
                
                // Optional: semantic filtering if target title provided
                if (!empty($targetTitle)) {
                    // Extract title/context from surrounding HTML if available
                    $pattern = '/' . preg_quote($url, '/') . '.*?&quot;t&quot;:&quot;([^"]+)&quot;/s';
                    if (preg_match($pattern, $html, $titleMatch)) {
                        $itemTitle = html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (!$this->isResultSemanticallyRelevant($targetTitle, $itemTitle, $url)) {
                            continue;
                        }
                    }
                }

                $images[] = $url;
                if (count($images) >= $limit) {
                    break;
                }
            }
        }

        return $images;
    }

    /**
     * Apify actor photo scraper (lurkapi/google-maps-photos-and-videos-scraper)
     */
    protected function scrapeFromApifyActor(string $placeId, string $token, int $limit): array
    {
        $actorId = 'KpQF2B9mcQFkExt8X';
        $url = "https://api.apify.com/v2/acts/{$actorId}/runs";

        $runRes = Http::withoutVerifying()->withToken($token)->timeout(25)->post($url, [
            'categories' => ['all'],
            'imageSize' => 'large',
            'maxPhotosPerPlace' => $limit,
            'maxPlacesPerSearch' => 1,
            'outputDimensions' => true,
            'outputGps' => true,
            'outputInternalCategoryCode' => false,
            'outputPlaceIds' => true,
            'outputPlaceName' => true,
            'outputPlaceUrl' => true,
            'outputScrapedAt' => true,
            'outputSearchKeyword' => true,
            'outputSource' => true,
            'outputThumbnailUrl' => true,
            'outputUploadDate' => true,
            'outputUploadedByOwner' => true,
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
            // Ignore non-photo records if type is specified
            if (isset($item['type']) && $item['type'] !== 'photo') {
                continue;
            }

            $imgUrl = $item['imageUrl'] ?? $item['photoUrl'] ?? $item['photo_url_large'] ?? $item['photo_url'] ?? $item['thumbnailUrl'] ?? $item['url'] ?? null;
            if ($imgUrl && $this->isValidImageUrl($imgUrl)) {
                $images[] = $imgUrl;
            }
        }

        return $images;
    }

    /**
     * Check if a search result title/URL is semantically relevant to the venue name.
     */
    public function isResultSemanticallyRelevant(string $venueTitle, string $resultTitle, string $resultUrl = ''): bool
    {
        $cleanVenue = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $venueTitle)));
        $venueWords = array_filter(explode(' ', $cleanVenue), fn ($w) => strlen($w) >= 3);

        // Generic stopwords that don't uniquely identify the place
        $stopwords = [
            'hotel', 'cafe', 'kafe', 'resto', 'restoran', 'villa', 'resort', 'homestay',
            'guest', 'house', 'guesthouse', 'warung', 'kopi', 'coffee', 'shop', 'the', 'and', 'bar',
            'jalan', 'kab', 'kabupaten', 'kota', 'provinsi', 'indonesia', 'tempat', 'wisata',
            'space', 'lounge', 'dining', 'kitchen', 'spot', 'center', 'room', 'rooms', 'kan', 'dan'
        ];

        $distinctiveWords = array_values(array_diff($venueWords, $stopwords));
        $haystack = strtolower($resultTitle . ' ' . $resultUrl);

        if (!empty($distinctiveWords)) {
            // First pass: strict whole-word match
            foreach ($distinctiveWords as $dw) {
                if (strlen($dw) >= 4 && str_contains($haystack, $dw)) {
                    return true;
                }
                if (preg_match('/\b' . preg_quote($dw, '/') . '\b/i', $haystack)) {
                    return true;
                }
            }
            
            // Second pass: relaxed partial match for unique/long words (≥6 chars)
            // This helps with unique venue names that might have slight variations
            foreach ($distinctiveWords as $dw) {
                if (strlen($dw) >= 6) {
                    // Allow 70% substring match for unique long words
                    $minMatch = max(4, (int)(strlen($dw) * 0.7));
                    $substring = substr($dw, 0, $minMatch);
                    if (str_contains($haystack, $substring)) {
                        return true;
                    }
                }
            }
            
            return false;
        }

        // If venue has only generic words (e.g. "The Coffee Bar"), match the combined clean title
        return str_contains($haystack, $cleanVenue);
    }

    /**
     * Strict validation for photo URLs to filter out cars, engines, auction marketplaces, cartoons, clipart, vectors, and wallpapers.
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

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        if (str_ends_with($host, '.jp') || str_contains($host, '.jp/')) {
            return false;
        }

        $lower = strtolower($url . ' ' . $contextTitle);

        // Blacklisted keywords & domains for irrelevant non-venue artifacts
        $blacklisted = [
            // Generic graphics, logos & icons
            'favicon', 'logo', 'icon.', 'icon-', '.ico', '.svg', '.gif', 'og-', 'og_image',
            'avatar', 'placeholder', 'no-image', 'no_image', 'default-',
            
            // Cartoons, Anime, Games & Pets
            'disney', 'cartoon', 'fandom.com', 'wikia.nocookie', 'anime', 'manga', 'battlecat', 'wikitide',
            'clipart', 'vector', 'drawing', 'coloring-page', 'coloring_page',
            'cats.com', 'petpress.net', 'catway.net', 'catbreedselector', 'dogtime', 'thesprucepets',
            'cat-breed', 'kitten', 'puppy',

            // Stock photo & Wallpaper platforms
            'pinimg.com', 'pinterest.com', 'craiyon.com', 'wallpapers.com',
            'pngwing', 'pngtree', 'freepik', 'cleanpng', 'stickers',
            'deviantart', 'fanart', 'pngimg', 'clipartkey', 'pinclipart',
            'wallpapercave', 'wallpaperflare', 'alphacoders', 'desktopnexus',
            'danbooru', 'zerochan', 'pixiv', 'flaticon', 'vexels',
            'vecteezy', 'vectorstock', 'shutterstock', 'istockphoto', 'dreamstime', '123rf', 'depositphotos',

            // Automotive, Engines, Auctions & Cars
            'honda', 'n-box', 'nbox', 'toyota', 'daihatsu', 'suzuki', 'mitsubishi',
            'yamaha', 'kawasaki', 'engine', 'sparepart', 'mesin-mobil', 'mobil-', 'motor-',
            'hondacars', 'nboxforlife', 'autotrader', 'caranddriver', 'motor1', 'autoevolution',
            'aucfree', 'afimg', 'ameba', 'celica', 'auction', 'lelang',

            // Marketplaces, E-commerce, Supplements & Apparel
            'shopify.com', 'myshopify', 'nutrition', 'supplement', 'protein', 'gymflooring',
            'yimg.jp', 'mercdn.net', 'mercari', 'yahoo.co.jp', 'rakuten', 'dmm.co.jp',
            'tokopedia', 'shopee', 'lazada', 'bukalapak', 'ebay', 'amazon', 'aliexpress',
            'alibaba', 'carousell', 'olx.co.id', 'blibli', 'zalora',
            't-shirt', 'kaos', 'baju', 'sepatu', 'jaket', 'dress', 'clothing', 'apparel',

            // Gossip / Celebrity / Foreign Cricket news
            'zeenews', 'timesnownews', 'scoopwhoop', 'ndtvimg', 'uccricket', 'cricbuzz',
            'espncricinfo', 'crictracker', 'sportskeeda', 'virat-kohli', 'karan-johar', 'bollywood',
        ];

        foreach ($blacklisted as $bad) {
            if (str_contains($lower, $bad)) {
                return false;
            }
        }

        return true;
    }
}
