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
    protected bool $enrichPhotos = false;

    /**
     * Import directly from an array of Google Maps records (e.g. from Apify Crawler)
     */
    public function importFromRecords(array $records, array $options = []): array
    {
        $this->resetStats();

        if (isset($options['skip_duplicates'])) {
            $this->skipDuplicates = (bool) $options['skip_duplicates'];
        }
        if (isset($options['enrich_photos'])) {
            $this->enrichPhotos = (bool) $options['enrich_photos'];
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

            // Extract all Google Maps image URLs
            $mapsImages = self::extractImageUrls($data);

            // Optional multi-source scraping to enrich photos (disabled by default to prevent slow requests)
            $combinedImages = $mapsImages;
            if ($this->enrichPhotos && !empty($data['title'])) {
                try {
                    $photoScraper = app(PhotoScraperService::class);
                    $queryStr = $data['title'];
                    if (!empty($data['address'])) {
                        $queryStr .= ' ' . $data['address'];
                    }
                    $combinedImages = $photoScraper->scrapePhotos($queryStr, 12, [
                        'place_id' => $data['placeId'] ?? null,
                        'existing_photos' => $mapsImages,
                    ]);
                } catch (\Throwable) {
                    $combinedImages = $mapsImages;
                }
            }

            // Save related data
            if ($postId) {
                $this->savePostImages($postId, $combinedImages);
                $this->savePostDetails($postId, $data);

                // Ensure post thumbnail is set if currently empty
                if (!empty($combinedImages[0])) {
                    Post::where('id', $postId)->whereNull('thumbnail')->update(['thumbnail' => $combinedImages[0]]);
                }
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
        $addressStr = $data['address'] ?? ($data['location'] ?? '');
        $location = $this->parseAddressToLocation($addressStr);
        $provinceId = $this->overrideProvinceId ?: $location['province_id'];
        $cityId = $this->overrideCityId ?: $location['city_id'];

        // Find category with fallback to title keywords & default category
        $categoryId = $this->defaultCategoryId 
            ?: $this->findCategoryByName($data['category'] ?? '', $data['title'] ?? '');

        if (!$categoryId) {
            $categoryId = Category::query()->first()?->id;
        }

        // Generate slug
        $slug = Str::slug($data['title']);
        $slug = $this->ensureUniqueSlug($slug, $data['placeId'] ?? null);

        // Prepare excerpt from description
        $description = $data['description'] ?? ($data['snippet'] ?? '');
        $excerpt = Str::limit(strip_tags($description), 200);

        $status = $this->defaultStatus ?: 'draft';
        $publishedAt = $status === 'published' ? now() : null;

        $isJob = false;
        if ($categoryId) {
            $cat = Category::find($categoryId);
            if ($cat && (str_contains(strtolower($cat->slug), 'lowongan') || str_contains(strtolower($cat->slug), 'loker') || str_contains(strtolower($cat->name), 'lowongan'))) {
                $isJob = true;
            }
        }
        $expiredAt = $isJob ? ($publishedAt ? $publishedAt->copy()->addDays(21) : now()->addDays(21)) : null;

        return [
            // Basic fields
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $description,
            'status' => $status,
            'published_at' => $publishedAt,
            'expired_at' => $expiredAt,

            // Location
            'category_id' => $categoryId,
            'province_id' => $provinceId,
            'city_id' => $cityId,

            // Google Maps / Job fields
            'place_id' => $data['placeId'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? ($data['apply_url'] ?? null),
            'rating' => $data['rating'] ?? null,
            'reviews_count' => $data['reviewsCount'] ?? 0,
            'google_maps_url' => $data['googleMapsUrl'] ?? null,
            'price_level' => $data['price'] ?? null,
            'cid' => $data['cid'] ?? null,

            // Use first image as thumbnail
            'thumbnail' => self::extractImageUrls($data)[0] ?? null,
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
     * Find category by name or title keywords (fuzzy matching)
     */
    protected function findCategoryByName(string $categoryName, string $title = ''): ?int
    {
        if (!empty($categoryName)) {
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
        }

        // Try detecting from title if categoryName is empty or not matched
        if (!empty($title)) {
            $lowerTitle = strtolower($title);
            $categories = Category::all();

            // First check if any category name is contained in the title
            foreach ($categories as $cat) {
                if (str_contains($lowerTitle, strtolower($cat->name))) {
                    return $cat->id;
                }
            }

            // Check common hospitality / culinary keywords
            if (preg_match('/\b(hotel|resort|villa|homestay|guest house|guesthouse|inn|penginapan|hostel)\b/i', $lowerTitle)) {
                $hotelCat = Category::where('name', 'LIKE', '%hotel%')
                    ->orWhere('name', 'LIKE', '%penginapan%')
                    ->orWhere('name', 'LIKE', '%stay%')
                    ->first();
                if ($hotelCat) return $hotelCat->id;
            }

            if (preg_match('/\b(kafe|cafe|kopi|coffee|roastery|warkop)\b/i', $lowerTitle)) {
                $cafeCat = Category::where('name', 'LIKE', '%kafe%')
                    ->orWhere('name', 'LIKE', '%cafe%')
                    ->orWhere('name', 'LIKE', '%kopi%')
                    ->first();
                if ($cafeCat) return $cafeCat->id;
            }

            if (preg_match('/\b(resto|restoran|restaurant|warung|kuliner|bakso|mie|ayam|soto|seafood|dapur)\b/i', $lowerTitle)) {
                $restoCat = Category::where('name', 'LIKE', '%kuliner%')
                    ->orWhere('name', 'LIKE', '%resto%')
                    ->orWhere('name', 'LIKE', '%makan%')
                    ->first();
                if ($restoCat) return $restoCat->id;
            }

            if (preg_match('/\b(wisata|taman|pantai|curug|museum|candi|waterpark|spot)\b/i', $lowerTitle)) {
                $wisataCat = Category::where('name', 'LIKE', '%wisata%')
                    ->orWhere('name', 'LIKE', '%destinasi%')
                    ->orWhere('name', 'LIKE', '%rekreasi%')
                    ->first();
                if ($wisataCat) return $wisataCat->id;
            }

            if (preg_match('/\b(lowongan|karir|career|recruitment|rekrutmen|dibutuhkan|hiring|loker\b|job vacancy)\b/i', $lowerTitle)) {
                $jobCat = Category::where('name', 'LIKE', '%lowongan%')
                    ->orWhere('name', 'LIKE', '%karir%')
                    ->orWhere('name', 'LIKE', '%loker%')
                    ->first();
                if ($jobCat) return $jobCat->id;
            }

            if (preg_match('/\b(penitipan|bagasi|koper|shower|mandi|coworking|apotek|lounge|transit|stasiun|bandara|terminal|laundry|atm|money changer|shuttle)\b/i', $lowerTitle)) {
                $fasilitasCat = Category::where('name', 'LIKE', '%fasilitas%')
                    ->orWhere('name', 'LIKE', '%transit%')
                    ->first();
                if ($fasilitasCat) return $fasilitasCat->id;
            }
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

        // Custom details array (e.g. from Job extractor: posisi, perusahaan, gaji, dll.)
        if (!empty($data['details']) && is_array($data['details'])) {
            foreach ($data['details'] as $k => $v) {
                if (!empty($v) && is_scalar($v)) {
                    $details[] = [
                        'post_id' => $postId,
                        'meta_key' => (string) $k,
                        'meta_value' => (string) $v,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
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
     * Set enrich photos flag (external photo scraping)
     */
    public function setEnrichPhotos(bool $enrich): self
    {
        $this->enrichPhotos = $enrich;
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
     * Extract normalized image URLs from various Apify/Google Maps data structures.
     */
    public static function extractImageUrls(array $data): array
    {
        $list = [];

        // Check all possible photo keys in Apify / Google Places data
        $candidates = [
            $data['imageUrls'] ?? null,
            $data['images'] ?? null,
            $data['photos'] ?? null,
            $data['placePhotos'] ?? null,
            $data['photoUrls'] ?? null,
            $data['imageUrl'] ?? null,
            $data['image'] ?? null,
            $data['photo'] ?? null,
            $data['featuredImage'] ?? null,
            $data['coverImage'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (empty($candidate)) {
                continue;
            }

            if (is_string($candidate)) {
                // If it's a JSON string, try decoding it
                if (str_starts_with($candidate, '[') || str_starts_with($candidate, '{')) {
                    $decoded = json_decode($candidate, true);
                    if (is_array($decoded)) {
                        $candidate = $decoded;
                    }
                }
            }

            if (is_array($candidate)) {
                foreach ($candidate as $item) {
                    if (is_string($item) && filter_var($item, FILTER_VALIDATE_URL)) {
                        $list[] = $item;
                    } elseif (is_array($item)) {
                        $url = $item['url'] ?? $item['imageUrl'] ?? $item['photoUrl'] ?? $item['image'] ?? $item['link'] ?? null;
                        if ($url && is_string($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                            $list[] = $url;
                        }
                    }
                }
            } elseif (is_string($candidate) && filter_var($candidate, FILTER_VALIDATE_URL)) {
                $list[] = $candidate;
            }
        }

        return array_values(array_unique(array_filter($list, function ($url) {
            if (!is_string($url) || str_starts_with($url, 'data:')) return false;
            $lower = strtolower($url);
            return !str_contains($lower, 'favicon') && !str_contains($lower, 'logo');
        })));
    }
}

