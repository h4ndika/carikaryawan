<?php

namespace App\Services;

use App\Models\ApifyAccount;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class PhotoScraperService
{
    protected const DDG_IMAGE_SEARCH_URL = 'https://duckduckgo.com/i.js';
    protected const APIFY_PHOTO_ACTOR = 'solidcode/google-maps-photos-scraper';

    /**
     * Scrape place photos concurrently across multiple sources (DuckDuckGo, Bing, and optional Apify).
     *
     * @param string $query (e.g. "Warung Kopi Klotok Sleman Yogyakarta")
     * @param int $limit Max photos to return
     * @param array $options ['place_id' => '...', 'apify_token' => '...', 'existing_photos' => []]
     * @return array List of valid absolute image URLs
     */
    public function scrapePhotos(string $query, int $limit = 12, array $options = []): array
    {
        $query = trim(preg_replace('/\s+/', ' ', $query));
        if (empty($query)) {
            return $options['existing_photos'] ?? [];
        }

        $allImages = $options['existing_photos'] ?? [];

        // ── Parallel Search Scrapers (Bing + DuckDuckGo via concurrent HTTP Pool) ──
        try {
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('bing')
                    ->withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                        'Accept-Language' => 'id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7',
                    ])
                    ->timeout(12)
                    ->get('https://www.bing.com/images/search', [
                        'q' => $query,
                        'form' => 'HDRSC2',
                        'first' => '1',
                    ]),

                $pool->as('ddg_page')
                    ->withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    ])
                    ->timeout(10)
                    ->get('https://duckduckgo.com/', [
                        'q' => $query,
                        'iax' => 'images',
                        'ia' => 'images',
                    ]),
            ]);

            // Parse Bing images
            if (isset($responses['bing']) && $responses['bing']->successful()) {
                $html = $responses['bing']->body();
                if (preg_match_all('/murl&quot;:&quot;(https?:\/\/[^&"]+)&quot;/i', $html, $matches)) {
                    foreach ($matches[1] as $url) {
                        if ($this->isValidImageUrl($url) && !in_array($url, $allImages)) {
                            $allImages[] = $url;
                        }
                    }
                }
            }

            // Parse DDG VQD and fetch DDG images
            if (isset($responses['ddg_page']) && $responses['ddg_page']->successful()) {
                $ddgBody = $responses['ddg_page']->body();
                if (preg_match('/vqd=["\']?([^"\'\s&]+)/i', $ddgBody, $vMatches)) {
                    $vqd = $vMatches[1];
                    $ddgImages = $this->fetchDdgImagesWithVqd($query, $vqd, $limit);
                    foreach ($ddgImages as $url) {
                        if (!in_array($url, $allImages)) {
                            $allImages[] = $url;
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            Log::debug("Parallel photo scrape notice for '{$query}': " . $e->getMessage());
        }

        // ── Apify Photo Scraper (Optional fallback if still < 3 images) ──
        $placeId = $options['place_id'] ?? null;
        if (count($allImages) < 3 && !empty($placeId) && str_starts_with($placeId, 'ChI')) {
            try {
                $token = $options['apify_token'] ?? null;
                if (!$token) {
                    $acc = ApifyAccount::getNextActiveAccount();
                    $token = $acc?->token ?? config('services.apify.token') ?? env('APIFY_TOKEN');
                }

                if ($token) {
                    $apifyImages = $this->scrapeFromApifyActor($placeId, $token, $limit);
                    foreach ($apifyImages as $img) {
                        if (!in_array($img, $allImages)) {
                            $allImages[] = $img;
                        }
                    }
                }
            } catch (Throwable) {}
        }

        return array_slice(array_values(array_unique($allImages)), 0, $limit);
    }

    /**
     * Scrape photos for a specific Post model and attach them to the post and post_images table.
     */
    public function scrapeAndAttachForPost(
        Post $post,
        int $limit = 12,
        bool $replaceExisting = false,
        bool $publishOnComplete = false
    ): array {
        // Build an accurate, descriptive location search query
        $queryParts = [$post->title];
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

        // Update post thumbnail if currently empty
        $updateData = [];
        if (empty($post->thumbnail) && !empty($photos[0])) {
            $updateData['thumbnail'] = $photos[0];
        }

        if ($publishOnComplete) {
            $updateData['status'] = 'published';
            if (empty($post->published_at)) {
                $updateData['published_at'] = now();
            }
        }

        if (!empty($updateData)) {
            $post->update($updateData);
        }

        $totalPhotosNow = PostImage::where('post_id', $post->id)->count();

        return [
            'success' => true,
            'message' => "Berhasil menambahkan {$insertedCount} foto baru (Total: {$totalPhotosNow} foto).",
            'count' => $insertedCount,
            'total' => $totalPhotosNow,
            'photos' => $photos,
        ];
    }

    /**
     * Fetch DDG images using VQD
     */
    protected function fetchDdgImagesWithVqd(string $query, string $vqd, int $limit): array
    {
        $response = Http::withoutVerifying()->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Referer' => 'https://duckduckgo.com/',
        ])->timeout(10)->get(self::DDG_IMAGE_SEARCH_URL, [
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
            if ($url && $this->isValidImageUrl($url)) {
                $images[] = $url;
                if (count($images) >= $limit) {
                    break;
                }
            }
        }

        return $images;
    }

    /**
     * Apify actor photo scraper
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
     * Validate image URL to avoid broken/icon URLs
     */
    public function isValidImageUrl(?string $url): bool
    {
        if (empty($url) || !is_string($url)) {
            return false;
        }

        if (str_starts_with($url, 'data:')) {
            return false;
        }

        $lower = strtolower($url);
        if (str_contains($lower, 'favicon') || str_contains($lower, 'logo') || str_contains($lower, 'icon.png')) {
            return false;
        }

        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }
}
