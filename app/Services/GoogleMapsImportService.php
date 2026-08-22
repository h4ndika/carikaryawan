<?php

namespace App\Services;

use App\Helpers\AddressParser;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\PostImage;
use App\Models\Province;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleMapsImportService
{
    protected array $stats = [
        'imported' => 0,
        'updated' => 0,
        'skipped' => 0,
        'failed' => 0,
    ];

    protected bool $skipDuplicates = false;
    protected int $batchSize = 100;
    protected ?int $defaultCategoryId = null;
    protected ?int $overrideCityId = null;
    protected ?int $overrideProvinceId = null;
    protected string $defaultStatus = 'draft';

    /**
     * Import directly from an array of Google Maps records (e.g. from Apify Crawler)
     */
    public function importFromRecords(array $records, array $options = []): array
    {
        $this->resetStats();

        if (isset($options['skip_duplicates'])) {
            $this->skipDuplicates = (bool) $options['skip_duplicates'];
        }
        if (isset($options['category_id'])) {
            $this->defaultCategoryId = (int) $options['category_id'];
        }
        if (isset($options['city_id'])) {
            $this->overrideCityId = (int) $options['city_id'];
            $city = City::find($this->overrideCityId);
            if ($city) {
                $this->overrideProvinceId = $city->province_id;
            }
        }
        if (isset($options['status'])) {
            $this->defaultStatus = $options['status'];
        }

        if (empty($records)) {
            return $this->stats;
        }

        $this->processRecords($records);

        return $this->stats;
    }

    /**
     * Import from directory containing JSON files
     */
    public function importFromDirectory(string $directory): array
    {
        $this->resetStats();

        if (!is_dir($directory)) {
            Log::error("Directory not found: {$directory}");
            return $this->stats;
        }

        $jsonFiles = glob($directory . '/*.json');

        if (empty($jsonFiles)) {
            Log::warning("No JSON files found in: {$directory}");
            return $this->stats;
        }

        foreach ($jsonFiles as $filePath) {
            $this->importFromFile($filePath);
        }

        return $this->stats;
    }

    /**
     * Import from a single JSON file
     */
    public function importFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("File not found: {$filePath}");
            $this->stats['failed']++;
            return $this->stats;
        }

        try {
            $data = $this->parseJsonFile($filePath);
            
            if (empty($data)) {
                Log::warning("No data found in: {$filePath}");
                return $this->stats;
            }

            $this->processRecords($data);

        } catch (\Exception $e) {
            Log::error("Error importing file {$filePath}: " . $e->getMessage());
            $this->stats['failed']++;
        }

        return $this->stats;
    }

    /**
     * Parse JSON file and return array of records
     */
    protected function parseJsonFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: " . json_last_error_msg());
        }

        // Handle both array of objects and single object
        if (isset($data[0])) {
            return $data;
        } elseif (is_array($data) && !empty($data)) {
            return [$data];
        }

        return [];
    }

    /**
     * Process records in batches
     */
    protected function processRecords(array $records): void
    {
        $chunks = array_chunk($records, $this->batchSize);

        foreach ($chunks as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $record) {
                    $this->processRecord($record);
                }
            });
        }
    }

    /**
     * Process a single record
     */
    protected function processRecord(array $data): void
    {
        try {
            // Check for required fields
            if (empty($data['title'])) {
                Log::warning("Skipping record: missing title", $data);
                $this->stats['skipped']++;
                return;
            }

            // Check for duplicates
            $placeId = $data['placeId'] ?? null;
            if ($placeId && $this->isDuplicate($placeId)) {
                if ($this->skipDuplicates) {
                    Log::info("Skipping duplicate place_id: {$placeId}");
                    $this->stats['skipped']++;
                    return;
                }
                // Update existing
                $postId = $this->updatePost($placeId, $data);
                $this->stats['updated']++;
            } else {
                // Create new
                $postId = $this->createPost($data);
                $this->stats['imported']++;
            }

            // Save related data
            if ($postId) {
                $this->savePostImages($postId, $data['imageUrls'] ?? []);
                $this->savePostDetails($postId, $data);
            }

        } catch (\Exception $e) {
            Log::error("Error processing record: " . $e->getMessage(), $data);
            $this->stats['failed']++;
        }
    }

    /**
     * Map JSON data to post attributes
     */
    protected function mapJsonToPost(array $data): array
    {
        // Parse address to get location
        $location = $this->parseAddressToLocation($data['address'] ?? '');
        $provinceId = $this->overrideProvinceId ?: $location['province_id'];
        $cityId = $this->overrideCityId ?: $location['city_id'];

        // Find category
        $categoryId = $this->findCategoryByName($data['category'] ?? '') ?: $this->defaultCategoryId;

        // Generate slug
        $slug = Str::slug($data['title']);
        $slug = $this->ensureUniqueSlug($slug, $data['placeId'] ?? null);

        // Prepare excerpt from description
        $description = $data['description'] ?? '';
        $excerpt = Str::limit(strip_tags($description), 200);

        $status = $this->defaultStatus ?: 'draft';
        $publishedAt = $status === 'published' ? now() : null;

        return [
            // Basic fields
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $description,
            'status' => $status,
            'published_at' => $publishedAt,

            // Location
            'category_id' => $categoryId,
            'province_id' => $provinceId,
            'city_id' => $cityId,

            // Google Maps fields
            'place_id' => $data['placeId'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'rating' => $data['rating'] ?? null,
            'reviews_count' => $data['reviewsCount'] ?? 0,
            'google_maps_url' => $data['googleMapsUrl'] ?? null,
            'price_level' => $data['price'] ?? null,
            'cid' => $data['cid'] ?? null,

            // Use first image as thumbnail
            'thumbnail' => $data['imageUrls'][0] ?? null,
        ];
    }

    /**
     * Parse address to province and city IDs
     */
    protected function parseAddressToLocation(string $address): array
    {
        if (empty($address)) {
            return ['province_id' => null, 'city_id' => null];
        }

        return AddressParser::parse($address);
    }

    /**
     * Find category by name (fuzzy matching)
     */
    protected function findCategoryByName(string $categoryName): ?int
    {
        if (empty($categoryName)) {
            return $this->defaultCategoryId;
        }

        // Try exact match first
        $category = Category::where('name', $categoryName)->first();
        if ($category) {
            return $category->id;
        }

        // Try case-insensitive match
        $category = Category::whereRaw('LOWER(name) = ?', [strtolower($categoryName)])->first();
        if ($category) {
            return $category->id;
        }

        // Try partial match
        $category = Category::where('name', 'LIKE', "%{$categoryName}%")->first();
        if ($category) {
            return $category->id;
        }

        // Return default or null
        return $this->defaultCategoryId;
    }

    /**
     * Check if place_id already exists
     */
    protected function isDuplicate(string $placeId): bool
    {
        return Post::where('place_id', $placeId)->exists();
    }

    /**
     * Create new post
     */
    protected function createPost(array $data): int
    {
        $postData = $this->mapJsonToPost($data);
        $post = Post::create($postData);
        
        return $post->id;
    }

    /**
     * Update existing post
     */
    protected function updatePost(string $placeId, array $data): int
    {
        $postData = $this->mapJsonToPost($data);
        $post = Post::where('place_id', $placeId)->first();
        
        if ($post) {
            $post->update($postData);
            return $post->id;
        }

        throw new \Exception("Post not found for place_id: {$placeId}");
    }

    /**
     * Save images to post_images table
     */
    protected function savePostImages(int $postId, array $imageUrls): void
    {
        if (empty($imageUrls)) {
            return;
        }

        // Delete existing images
        PostImage::where('post_id', $postId)->delete();

        // Insert new images
        foreach ($imageUrls as $index => $imageUrl) {
            PostImage::create([
                'post_id' => $postId,
                'image_url' => $imageUrl,
                'caption' => null,
                'order' => $index,
            ]);
        }
    }

    /**
     * Save additional details to post_details table
     */
    protected function savePostDetails(int $postId, array $data): void
    {
        // Delete existing details for this post
        PostDetail::where('post_id', $postId)->delete();

        $details = [];

        // Opening hours
        if (!empty($data['openingHours'])) {
            $details[] = [
                'post_id' => $postId,
                'meta_key' => 'jam_buka',
                'meta_value' => $data['openingHours'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Facilities
        if (!empty($data['facilities'])) {
            $details[] = [
                'post_id' => $postId,
                'meta_key' => 'fasilitas',
                'meta_value' => $data['facilities'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // CID (if not stored in main post)
        if (!empty($data['cid'])) {
            $details[] = [
                'post_id' => $postId,
                'meta_key' => 'google_cid',
                'meta_value' => $data['cid'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($details)) {
            PostDetail::insert($details);
        }
    }

    /**
     * Ensure slug is unique
     */
    protected function ensureUniqueSlug(string $slug, ?string $placeId = null): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (Post::where('slug', $slug)->exists()) {
            if ($placeId) {
                // Use place_id for uniqueness
                $slug = $originalSlug . '-' . substr($placeId, -8);
                break;
            } else {
                // Use counter
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        return $slug;
    }

    /**
     * Set skip duplicates flag
     */
    public function setSkipDuplicates(bool $skip): self
    {
        $this->skipDuplicates = $skip;
        return $this;
    }

    /**
     * Set batch size
     */
    public function setBatchSize(int $size): self
    {
        $this->batchSize = $size;
        return $this;
    }

    /**
     * Set default category ID
     */
    public function setDefaultCategoryId(?int $categoryId): self
    {
        $this->defaultCategoryId = $categoryId;
        return $this;
    }

    /**
     * Reset statistics
     */
    protected function resetStats(): void
    {
        $this->stats = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
    }

    /**
     * Get current statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }
}
