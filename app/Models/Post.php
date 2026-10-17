<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($post) {
            if ($post->status === 'published' && is_null($post->published_at)) {
                $post->published_at = now();
            }
        });

        static::updating(function ($post) {
            if ($post->status === 'published' && is_null($post->published_at)) {
                $post->published_at = now();
            }
        });
    }

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'views_count' => 'integer',
        'affiliate_links' => 'array',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
        'posted_to_threads_at' => 'datetime',
    ];

    public function pageViews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PageView::class);
    }

    /**
     * Determine if the post has any active affiliate links.
     */
    public function hasActiveAffiliateLinks(): bool
    {
        if (empty($this->affiliate_links) || !is_array($this->affiliate_links)) {
            return false;
        }

        foreach ($this->affiliate_links as $link) {
            if (!empty($link['url']) && ($link['is_active'] ?? true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get filtered active affiliate links.
     */
    public function getActiveAffiliateLinks(): array
    {
        if (empty($this->affiliate_links) || !is_array($this->affiliate_links)) {
            return [];
        }

        return array_values(array_filter($this->affiliate_links, function ($link) {
            return !empty($link['url']) && ($link['is_active'] ?? true);
        }));
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function details()
    {
        return $this->hasMany(PostDetail::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('order');
    }

    /**
     * Get the primary image URL (featured_image_url -> thumbnail -> first gallery image).
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        if (!empty($this->featured_image_url)) {
            return $this->featured_image_url;
        }
        if (!empty($this->thumbnail)) {
            return $this->thumbnail;
        }
        if ($this->relationLoaded('images')) {
            $firstImg = $this->images->first()?->image_url;
            if (!empty($firstImg)) {
                return $firstImg;
            }
        } else {
            $firstImg = $this->images()->first()?->image_url;
            if (!empty($firstImg)) {
                return $firstImg;
            }
        }

        // Auto-generate dynamic SVG cover banner for job listings
        if ($this->isJob() && !empty($this->slug)) {
            return route('job.image', ['slug' => $this->slug]);
        }

        return null;
    }

    /**
     * Get all alternative candidate image URLs for fallback cascade.
     */
    public function getFallbackImageUrlsAttribute(): array
    {
        $primary = !empty($this->featured_image_url) ? $this->featured_image_url : $this->thumbnail;
        $urls = [];

        if (!empty($this->thumbnail) && $this->thumbnail !== $primary) {
            $urls[] = $this->thumbnail;
        }

        $gallery = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        foreach ($gallery as $img) {
            if (!empty($img->image_url) && $img->image_url !== $primary && !in_array($img->image_url, $urls)) {
                $urls[] = $img->image_url;
            }
        }

        return $urls;
    }

    /**
     * Get a verified working image URL, avoiding hotlink-protected Google scraper URLs (gps-cs-s).
     */
    public function getVerifiedImageUrlAttribute(): ?string
    {
        $primary = $this->primary_image_url;
        if (!empty($primary) && !str_contains($primary, '/gps-cs-s/')) {
            return $primary;
        }

        foreach ($this->fallback_image_urls as $url) {
            if (!empty($url) && !str_contains($url, '/gps-cs-s/')) {
                return $url;
            }
        }

        // Return primary as last resort if no alternative exists
        return $primary;
    }

    /**
     * Extract human-friendly source domain from an image URL.
     */
    public static function extractImageSourceDomain(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (empty($host)) {
            return null;
        }

        $host = preg_replace('/^www\./i', '', strtolower($host));

        if (str_ends_with($host, 'googleusercontent.com') || str_ends_with($host, 'ggpht.com') || str_ends_with($host, 'google.com')) {
            return 'Google Maps';
        }

        if (str_ends_with($host, 'unsplash.com')) {
            return 'Unsplash';
        }

        if (str_ends_with($host, 'pexels.com')) {
            return 'Pexels';
        }

        if (str_ends_with($host, 'wikimedia.org') || str_ends_with($host, 'wikipedia.org')) {
            return 'Wikimedia';
        }

        return $host;
    }

    /**
     * Get the source domain name of the primary image.
     */
    public function getPrimaryImageSourceAttribute(): ?string
    {
        return static::extractImageSourceDomain($this->primary_image_url);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope query to filter out expired jobs (for homepage/catalogue).
     */
    /**
     * Limit to posts in a job category (see Category::scopeJobs).
     */
    public function scopeJobs($query)
    {
        return $query->whereHas('category', fn ($q) => $q->jobs());
    }

    public function scopeActiveJob($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expired_at')
              ->orWhere('expired_at', '>', now());
        });
    }

    /**
     * Canonical place_id for job posts, shared by JobStreet & Glints for cross-engine dedup.
     * Do NOT change the formula: existing rows were backfilled with it (carikaryawan:backfill-job-place-ids).
     */
    public static function jobPlaceId(string $company, string $position, string $city): string
    {
        $norm = fn (string $s) => strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
        $company = preg_replace('/^(pt\.?|cv\.?|ud\.?|yayasan)\s+/i', '', trim($company));

        return 'job_' . substr(md5($norm($company) . '_' . $norm($position) . '_' . $norm($city)), 0, 24);
    }

    /**
     * Determine if this post is a job listing.
     */
    public function isJob(): bool
    {
        $catSlug = strtolower($this->category?->slug ?? '');
        $catName = strtolower($this->category?->name ?? '');

        return str_contains($catSlug, 'lowongan')
            || str_contains($catName, 'lowongan')
            || str_contains($catSlug, 'loker')
            || str_contains($catName, 'loker');
    }

    /**
     * Determine if this job listing has passed its expiration date.
     */
    public function isExpired(): bool
    {
        if ($this->expired_at) {
            return $this->expired_at->isPast();
        }

        // Fallback: If it's a job and published more than 21 days ago
        if ($this->isJob() && $this->published_at) {
            return $this->published_at->addDays(21)->isPast();
        }

        return false;
    }

    /** SEO helpers — fallback ke field konten jika meta kosong. */
    public function seoTitle(): string
    {
        return $this->meta_title ?: (string) $this->title;
    }

    public function seoDescription(): string
    {
        return $this->meta_description ?: (string) ($this->excerpt ?? '');
    }

    public function seoImage(): string
    {
        // Khusus loker, gunakan dynamic banner versi raster PNG (1200x630) agar terbaca sempurna oleh
        // crawler OpenGraph Meta Threads, WhatsApp, Facebook, dan Twitter Card.
        if ($this->isJob()) {
            $img = $this->og_image ?: ($this->thumbnail ?: '');
            if (!empty($img) && !str_ends_with(strtolower($img), '.svg')) {
                return $img;
            }
            return route('job.image.png', ['slug' => $this->slug]);
        }

        $image = $this->og_image ?: (string) ($this->thumbnail ?: ($this->primary_image_url ?? ''));

        if (empty($image) || str_ends_with(strtolower($image), '.svg')) {
            return asset('android-chrome-512x512.png');
        }

        return $image;
    }

    public function seoRobots(): string
    {
        // Closed jobs stay reachable but must leave the index (Google Jobs guideline).
        if ($this->isJob() && $this->isExpired()) {
            return 'noindex,follow';
        }

        return $this->robots ?: 'index,follow';
    }

    /**
     * Helper to get a detail meta_value by list of candidate keys.
     */
    public function getDetailValue(array|string $keys): ?string
    {
        $keys = (array) $keys;
        if ($this->relationLoaded('details')) {
            $matched = $this->details->first(function ($d) use ($keys) {
                return in_array(strtolower(trim($d->meta_key)), $keys);
            });
            return $matched ? trim($matched->meta_value) : null;
        }

        $row = $this->details()
            ->whereIn('meta_key', $keys)
            ->first();

        return $row ? trim($row->meta_value) : null;
    }

    /**
     * Map job employment type to Schema.org standard (FULL_TIME, PART_TIME, CONTRACTOR, INTERN, etc.)
     */
    public function getJobEmploymentType(): string
    {
        $raw = strtolower((string) ($this->getDetailValue(['tipe_pekerjaan', 'employment_type', 'tipe', 'status_kerja']) ?? ''));

        if (str_contains($raw, 'part') || str_contains($raw, 'paruh')) {
            return 'PART_TIME';
        }
        if (str_contains($raw, 'kontrak') || str_contains($raw, 'contract')) {
            return 'CONTRACTOR';
        }
        if (str_contains($raw, 'magang') || str_contains($raw, 'intern')) {
            return 'INTERN';
        }
        if (str_contains($raw, 'freelance') || str_contains($raw, 'lepas')) {
            return 'OTHER';
        }

        return 'FULL_TIME';
    }

    /**
     * Extract or estimate baseSalary for Google JobPosting schema.
     */
    public function getJobBaseSalary(): ?array
    {
        $rawSalary = (string) ($this->getDetailValue(['gaji', 'salary', 'rentang_gaji', 'upah']) ?? '');

        // Cari angka nominal dalam teks gaji (contoh: 3.500.000, 3500000, 3,5 - 5 jt, dll)
        preg_match_all('/\d+[\d\.,]*/', $rawSalary, $matches);
        $numbers = [];
        if (!empty($matches[0])) {
            foreach ($matches[0] as $numStr) {
                $cleanNum = (float) str_replace(['.', ','], '', $numStr);
                // Handle ekspresi jutaan (misal: "3 jt" atau "4.5 juta")
                if ($cleanNum > 0 && $cleanNum < 100 && (str_contains($rawSalary, 'jt') || str_contains($rawSalary, 'juta'))) {
                    $cleanNum = $cleanNum * 1000000;
                }
                if ($cleanNum >= 100000) {
                    $numbers[] = (int) $cleanNum;
                }
            }
        }

        if (count($numbers) >= 2) {
            sort($numbers);
            $minVal = $numbers[0];
            $maxVal = end($numbers);
            return [
                '@type' => 'MonetaryAmount',
                'currency' => 'IDR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $minVal,
                    'maxValue' => $maxVal,
                    'unitText' => 'MONTH',
                ],
            ];
        } elseif (count($numbers) === 1 && $numbers[0] >= 500000) {
            return [
                '@type' => 'MonetaryAmount',
                'currency' => 'IDR',
                'value' => [
                    '@type' => 'QuantitativeValue',
                    'value' => $numbers[0],
                    'unitText' => 'MONTH',
                ],
            ];
        }

        // Gaji tidak diketahui: jangan dikarang (Google menganggap data palsu sebagai pelanggaran).
        return null;
    }

    /**
     * Generate Schema.org JSON-LD Structured Data for this post.
     */
    public function generateJsonLd(?string $canonicalUrl = null): array
    {
        $postUrl = $canonicalUrl ?: $this->url();
        $siteUrl = url('/');
        $cityName = $this->city?->name ?? '';
        $provName = $this->province?->name ?? '';

        $images = array_values(array_filter([
            $this->primary_image_url,
            $this->seoImage(),
        ]));

        $graph = [];

        // 1. Article Schema
        $articleSchema = [
            '@type' => 'Article',
            '@id' => $postUrl . '#article',
            'isPartOf' => [
                '@type' => 'WebPage',
                '@id' => $postUrl,
            ],
            'headline' => $this->seoTitle(),
            'description' => $this->seoDescription(),
            'mainEntityOfPage' => $postUrl,
            'datePublished' => optional($this->published_at)->toIso8601String() ?? now()->toIso8601String(),
            'dateModified' => optional($this->updated_at)->toIso8601String() ?? now()->toIso8601String(),
            'inLanguage' => 'id-ID',
            'author' => [
                '@type' => 'Organization',
                'name' => 'Redaksi CariKaryawan',
                'url' => $siteUrl,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'CariKaryawan',
                'url' => $siteUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('android-chrome-512x512.png'),
                ],
            ],
        ];
        if (!empty($images)) {
            $articleSchema['image'] = $images;
        }
        $graph[] = $articleSchema;

        // Loker tutup: tanpa JobPosting sama sekali (halaman juga noindex via seoRobots()).
        if ($this->isJob() && ! $this->isExpired()) {
            // validThrough harus sama dengan aturan isExpired(); jangan pernah digeser maju.
            $validThrough = ($this->expired_at ?: ($this->published_at ?: $this->created_at ?: now())->copy()->addDays(21))
                ->toIso8601String();

            // Ekstrak nama perusahaan dan posisi secara presisi
            $companyName = $this->company_name ?: ($this->getDetailValue(['perusahaan', 'nama_perusahaan', 'company']) ?: '');
            $position = $this->getDetailValue('posisi');
            $jobTitle = $position ?: $this->title;
            if (!$position && $companyName) {
                $jobTitle = preg_replace('/\s+[-–—]\s+' . preg_quote($companyName, '/') . '\s*$/iu', '', $jobTitle);
            }
            if (empty($companyName)) {
                $companyName = $this->title;
            }

            // Lokasi kota bukan alamat jalan; jangan mengarang alamat atau kode pos.
            $streetAddress = trim($this->formatted_address ?: ($this->getDetailValue(['alamat', 'street_address']) ?: ''));
            if (in_array(mb_strtolower($streetAddress), array_map('mb_strtolower', [$cityName, $provName, 'Indonesia']), true)) {
                $streetAddress = '';
            }
            $postalCode = $this->getDetailValue(['kode_pos', 'postal_code']);

            $jobSchema = [
                '@type' => 'JobPosting',
                '@id' => $postUrl . '#jobposting',
                'title' => $jobTitle,
                'description' => strip_tags($this->content ?: $this->seoDescription()),
                'datePosted' => optional($this->published_at)->toIso8601String() ?? now()->toIso8601String(),
                'employmentType' => $this->getJobEmploymentType(),
                // Pelamar diarahkan ke situs sumber, jadi bukan direct apply.
                'directApply' => false,
                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => $companyName,
                ],
                'jobLocation' => [
                    '@type' => 'Place',
                    'address' => array_filter([
                        '@type' => 'PostalAddress',
                        'streetAddress' => $streetAddress,
                        'addressLocality' => $cityName,
                        'addressRegion' => $provName,
                        'postalCode' => $postalCode,
                        'addressCountry' => 'ID',
                    ]),
                ],
            ];

            $jobSchema['validThrough'] = $validThrough;
            if ($salary = $this->getJobBaseSalary()) {
                $jobSchema['baseSalary'] = $salary;
            }

            $graph[] = $jobSchema;
        }

        // 3. BreadcrumbList Schema
        $breadcrumbs = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => $siteUrl,
            ],
        ];

        $pos = 2;
        if ($this->category) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $this->category->name,
                'item' => $this->categoryUrl(),
            ];
        }

        if ($this->city && $this->category) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $this->city->name,
                'item' => $this->categoryWithCityUrl(),
            ];
        }

        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => $this->title,
            'item' => $postUrl,
        ];

        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $postUrl . '#breadcrumb',
            'itemListElement' => $breadcrumbs,
        ];

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * Scope query to posts with a specific place_id
     */
    public function scopeByPlaceId($query, string $placeId)
    {
        return $query->where('place_id', $placeId);
    }

    /**
     * Check if post has a Google Maps place_id
     */
    public function hasPlaceId(): bool
    {
        return !empty($this->place_id);
    }

    /**
     * Generate subdomain-based URL for this post
     */
    public function url(): string
    {
        return route('posts.show', [
            'category' => $this->category?->slug ?? 'lowongan-kerja',
            'city' => $this->city?->slug ?? 'indonesia',
            'slug' => $this->slug,
        ]);
    }

    /**
     * Generate category listing URL for this post's category
     */
    public function categoryUrl(): string
    {
        return route('category.home', [
            'category' => $this->category->slug,
        ]);
    }

    /**
     * Generate category + city listing URL
     */
    public function categoryWithCityUrl(): string
    {
        return route('posts.list', [
            'category' => $this->category->slug,
            'city' => $this->city->slug,
        ]);
    }
}
