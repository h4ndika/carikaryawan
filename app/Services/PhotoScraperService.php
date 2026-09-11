<?php

namespace App\Services;

use App\Exceptions\PhotoScrapeException;
use App\Models\ApifyAccount;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhotoScraperService
{
    protected const DDG_IMAGE_SEARCH_URL = 'https://duckduckgo.com/i.js';
    protected const APIFY_PHOTO_ACTOR = 'compass~crawler-google-places';

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
                    $token = $acc?->token ?? config('services.apify.token');
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
        // Handle job listings: search company logo instead of Google Maps venue photos
        if ($post->isJob()) {
            if (!$replaceExisting && !empty($post->thumbnail)) {
                return [
                    'success' => true,
                    'message' => "Lowongan kerja sudah memiliki logo/thumbnail: {$post->title}",
                    'count' => 0,
                    'total' => max(1, $post->images()->count()),
                ];
            }

            $companyName = $this->getJobCompanyName($post);
            $logoUrl = $this->searchCompanyLogo($companyName);

            if ($logoUrl) {
                $post->update(['thumbnail' => $logoUrl]);

                if ($post->images()->where('image_url', $logoUrl)->doesntExist()) {
                    $post->images()->create([
                        'image_url' => $logoUrl,
                        'caption' => "Logo {$companyName}",
                        'order' => 0,
                    ]);
                }

                if ($publishOnComplete) {
                    $post->update([
                        'status' => 'published',
                        'published_at' => $post->published_at ?? now(),
                    ]);
                }

                return [
                    'success' => true,
                    'message' => "Berhasil menemukan logo perusahaan untuk: {$companyName}",
                    'count' => 1,
                    'total' => max(1, $post->images()->count()),
                ];
            }

            return [
                'success' => true,
                'message' => "Logo perusahaan tidak ditemukan untuk: {$companyName} (menggunakan kartu standar).",
                'count' => 0,
                'total' => max(0, $post->images()->count()),
            ];
        }

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

        return $this->attachPhotosToPost($post, $photos, $replaceExisting, $publishOnComplete);
    }

    /**
     * Attach an array of photo URLs to a Post and post_images table.
     */
    public function attachPhotosToPost(
        Post $post,
        array $photos,
        bool $replaceExisting = false,
        bool $publishOnComplete = false
    ): array {
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
            'success' => $totalPhotosNow >= 1,
            'message' => $totalPhotosNow >= 1 
                ? "Berhasil menambahkan {$insertedCount} foto baru (Total: {$totalPhotosNow} foto)."
                : "Hanya tersimpan {$totalPhotosNow} foto.",
            'count' => $insertedCount,
            'total' => $totalPhotosNow,
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
     * Get next active Apify account and token from database or config.
     *
     * @return array{0: ?ApifyAccount, 1: ?string}
     */
    public function getApifyAccountAndToken(): array
    {
        $acc = ApifyAccount::getNextActiveAccount();
        $token = $acc?->token ?? config('services.apify.token');
        return [$acc, $token];
    }

    /**
     * Get active Apify token from database or config.
     */
    public function getApifyToken(): ?string
    {
        [, $token] = $this->getApifyAccountAndToken();
        return $token;
    }

    /**
     * Synchronous compatibility wrapper for non-queue callers; workers use start/poll methods.
     *
     * @param array<string> $placeIds
     * @param ?string $token
     * @param int $limitPerPlace
     * @return array<string, array<string>> Keyed by place_id => array of photo URLs
     */
    public function scrapeBatchFromApifyActor(array $placeIds, ?string $token = null, int $limitPerPlace = 8): array
    {
        // Compatibility for import callers. Queue workers start/poll independently.
        if (empty($placeIds)) {
            return [];
        }
        $run = $this->startPhotoRun($placeIds, $token, $limitPerPlace);
        $deadline = time() + 240;
        do {
            if ($this->photoRunStatus($run['id'], $run['token']) === 'SUCCEEDED') {
                return $this->fetchPhotoRunResults($run['dataset_id'], $run['token'], $placeIds, $limitPerPlace);
            }
            sleep(3);
        } while (time() < $deadline);
        throw new PhotoScrapeException("Apify run {$run['id']} belum selesai dalam 240 detik.");
    }

    /** Never retry ambiguous connection failures: a paid run may already exist. */
    public function startPhotoRun(array $placeIds, ?string $token = null, int $limit = 8): array
    {
        $lastError = 'Tidak ada akun/token Apify aktif. Periksa menu Akun Apify.';
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $account = null;
            if (!$token) {
                [$account, $token] = $this->getApifyAccountAndToken();
            }
            if (!$token) {
                throw new PhotoScrapeException($lastError);
            }
            try {
                $startUrls = array_map(
                    fn ($pid) => ['url' => "https://www.google.com/maps/place/?q=place_id:{$pid}"],
                    array_values(array_unique($placeIds))
                );

                $response = Http::withToken($token)->connectTimeout(5)->timeout(10)
                    ->post('https://api.apify.com/v2/acts/'.self::APIFY_PHOTO_ACTOR.'/runs?timeout='.PhotoScrapeQueue::RUN_SECONDS, [
                        'startUrls' => $startUrls,
                        'maxCrawledPlacesPerSearch' => count($startUrls),
                        'maxImages' => max(1, min(100, $limit)),
                        'scrapeReviews' => false,
                        'scrapePhotos' => true,
                    ]);
            } catch (Throwable $e) {
                throw new PhotoScrapeException('Koneksi start Apify gagal/timeout. Periksa Runs di Apify sebelum mengulang; run mungkin sudah dibuat.');
            }
            if ($response->successful()) {
                $run = $response->json('data');
                if (!is_array($run) || empty($run['id']) || empty($run['defaultDatasetId'])) {
                    throw new PhotoScrapeException('Respons start Apify tidak memiliki run ID/dataset ID.');
                }
                $account?->recordUsage();
                return ['id' => $run['id'], 'dataset_id' => $run['defaultDatasetId'], 'token' => $token];
            }
            $lastError = $this->photoApiError($response, 'start');
            $type = (string) $response->json('error.type');
            $quota = $response->status() === 402 || $type === 'monthly-usage-hard-limit-exceeded';
            $invalid = $response->status() === 401;
            if ($account && ($quota || $invalid)) {
                $account->update(['status' => $invalid ? 'invalid' : 'exhausted']);
                $token = null;
                continue;
            }
            throw new PhotoScrapeException($lastError);
        }
        throw new PhotoScrapeException($lastError);
    }

    /** One bounded poll; transient actor states are not treated as failures. */
    public function photoRunStatus(string $runId, string $token): string
    {
        $response = $this->photoGet("https://api.apify.com/v2/actor-runs/{$runId}", $token);
        if (!$response->successful()) {
            throw new PhotoScrapeException($this->photoApiError($response, 'status'), $response->status() === 429 || $response->serverError());
        }
        $status = $response->json('data.status');
        if (in_array($status, ['FAILED', 'ABORTED', 'TIMED-OUT'], true)) {
            throw new PhotoScrapeException("Apify run {$runId}: {$status}. Periksa log run di Apify.");
        }
        if (!in_array($status, ['READY', 'RUNNING', 'ABORTING', 'TIMING-OUT', 'SUCCEEDED'], true)) {
            throw new PhotoScrapeException("Status Apify run {$runId} tidak valid.");
        }
        return $status;
    }

    public function fetchPhotoRunResults(string $datasetId, string $token, array $placeIds, int $limit): array
    {
        $response = $this->photoGet("https://api.apify.com/v2/datasets/{$datasetId}/items", $token, [
                // Include the original batch even after some rows completed before a crash.
                'clean' => 'true', 'limit' => max(PhotoScrapeQueue::BATCH_SIZE, count($placeIds)) * 100,
            ]);
        if (!$response->successful()) {
            throw new PhotoScrapeException($this->photoApiError($response, 'dataset'), $response->status() === 429 || $response->serverError());
        }
        $items = $response->json();
        if (!is_array($items) || !is_array($items = $items['items'] ?? $items)) {
            throw new PhotoScrapeException('Dataset Apify bukan daftar JSON yang valid.');
        }
        $result = array_fill_keys($placeIds, []);
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $pid = $item['placeId'] ?? null;
            if (!$pid && count($placeIds) === 1) {
                $pid = $placeIds[0];
            }
            // Never assign a foreign place ID to another post.
            if (!$pid || !array_key_exists($pid, $result)) {
                continue;
            }

            // Support compass/crawler-google-places (imageUrls array) as well as flat item streams
            $itemImages = $item['imageUrls'] ?? $item['images'] ?? [];
            if (!is_array($itemImages) || empty($itemImages)) {
                $flatUrl = $item['imageUrl'] ?? $item['photoUrl'] ?? $item['photo_url_large'] ?? $item['photo_url'] ?? $item['thumbnailUrl'] ?? $item['url'] ?? null;
                if ($flatUrl) {
                    $itemImages = [$flatUrl];
                }
            }

            foreach ($itemImages as $rawImg) {
                $url = is_string($rawImg) ? $rawImg : ($rawImg['imageUrl'] ?? $rawImg['url'] ?? null);
                if (is_string($url) && $this->isValidImageUrl($url) && count($result[$pid]) < $limit && !in_array($url, $result[$pid], true)) {
                    $result[$pid][] = $url;
                }
            }
        }
        return $result;
    }

    private function photoGet(string $url, string $token, array $query = []): \Illuminate\Http\Client\Response
    {
        try {
            return Http::withToken($token)->connectTimeout(5)->timeout(20)->get($url, $query);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new PhotoScrapeException('Koneksi polling/dataset Apify gagal. Akan dicoba kembali secara terbatas.', true);
        }
    }

    private function photoApiError(\Illuminate\Http\Client\Response $response, string $stage): string
    {
        // Provider bodies can contain secrets; only expose machine error codes.
        $type = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $response->json('error.type', 'unknown'));
        $hint = match ($type) {
            'invalid-input' => 'Input actor ditolak. Kategori harus all/menu/food/vibe/by_owner/latest/videos/street_view.',
            'actor-memory-limit-exceeded' => 'Batas run/memori akun tercapai. Kurangi PHOTO_SCRAPE_MAX_ACTIVE_RUNS.',
            default => 'Periksa kuota, token, dan log Apify.',
        };
        return "Apify {$stage} HTTP {$response->status()} ({$type}): {$hint}";
    }


    /**
     * Apify actor photo scraper (lurkapi/google-maps-photos-and-videos-scraper)
     */
    protected function scrapeFromApifyActor(string $placeId, ?string $token = null, int $limit = 8): array
    {
        $batch = $this->scrapeBatchFromApifyActor([$placeId], $token, $limit);
        return $batch[$placeId] ?? [];
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

    /**
     * Extract company name from a job post details or title.
     */
    public function getJobCompanyName(Post $post): string
    {
        // 1. From post details
        $detail = $post->details?->first(function ($d) {
            $key = strtolower(trim($d->meta_key));
            return in_array($key, ['nama_perusahaan', 'perusahaan', 'company', 'company_name', 'employer']);
        });

        if ($detail && !empty(trim($detail->meta_value))) {
            return trim($detail->meta_value);
        }

        // 2. From title (e.g. "Posisi - Nama Perusahaan (Lokasi)" or "Posisi di Nama Perusahaan")
        if (str_contains($post->title, ' - ')) {
            $parts = explode(' - ', $post->title, 2);
            $company = trim($parts[1]);
            // Remove trailing parentheses like "(Bandung)"
            return trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $company));
        }

        if (preg_match('/\b(?:di|at)\s+([A-Za-z0-9\s&.,-]+)/i', $post->title, $matches)) {
            return trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $matches[1]));
        }

        return trim($post->title);
    }

    /**
     * Search company logo using Bing image search with fallback filtering.
     */
    public function searchCompanyLogo(string $companyName): ?string
    {
        $cleanCompany = trim(preg_replace('/^(PT|CV|UD|Toko|Warung|Resto|Cafe)\.?\s+/i', '', $companyName));
        $cleanCompany = trim(preg_replace('/\s*\([^)]*\)\s*/', ' ', $cleanCompany));

        if (empty($cleanCompany)) {
            return null;
        }

        $query = urlencode("{$cleanCompany} logo png");
        $url = "https://www.bing.com/images/search?q={$query}&form=HDRSC2&first=1";

        try {
            $res = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8',
            ])->timeout(12)->get($url);

            if (!$res->successful()) {
                return null;
            }

            $html = $res->body();
            if (preg_match_all('/murl&quot;:&quot;(https?:\/\/[^&"]+)&quot;/i', $html, $matches)) {
                $badPatterns = [
                    'freepik', 'vectorstock', 'shutterstock', 'stock', 'clipart',
                    'wallpapers', 'pinimg', 'facebook.com', 'tokopedia.net', '.jp',
                    'aerial-view', 'recipe'
                ];

                $slug = \Illuminate\Support\Str::slug($cleanCompany);
                $words = array_filter(explode('-', $slug), fn ($w) => strlen($w) >= 3);

                // Priority 1: Valid URL containing 'logo' and at least one company keyword
                foreach ($matches[1] as $imgUrl) {
                    if (!filter_var($imgUrl, FILTER_VALIDATE_URL)) continue;
                    $lower = strtolower($imgUrl);

                    $isBad = false;
                    foreach ($badPatterns as $bad) {
                        if (str_contains($lower, $bad)) {
                            $isBad = true;
                            break;
                        }
                    }
                    if ($isBad) continue;
                    if (!preg_match('/\.(png|jpe?g|webp)(\?|$)/i', $imgUrl)) continue;

                    $hasWord = false;
                    foreach ($words as $w) {
                        if (str_contains($lower, $w)) {
                            $hasWord = true;
                            break;
                        }
                    }

                    if (str_contains($lower, 'logo') && $hasWord) {
                        return $imgUrl;
                    }
                }

                // Priority 2: Valid URL containing 'logo'
                foreach ($matches[1] as $imgUrl) {
                    if (!filter_var($imgUrl, FILTER_VALIDATE_URL)) continue;
                    $lower = strtolower($imgUrl);

                    $isBad = false;
                    foreach ($badPatterns as $bad) {
                        if (str_contains($lower, $bad)) {
                            $isBad = true;
                            break;
                        }
                    }
                    if ($isBad) continue;

                    if (preg_match('/\.(png|jpe?g|webp)(\?|$)/i', $imgUrl) && str_contains($lower, 'logo')) {
                        return $imgUrl;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Company logo search failed for '{$companyName}': " . $e->getMessage());
        }

        return null;
    }
}
