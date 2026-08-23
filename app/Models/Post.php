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
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
        'published_at' => 'datetime',
    ];

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
            return $this->images->first()?->image_url;
        }
        return $this->images()->first()?->image_url;
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
        return $this->og_image ?: (string) ($this->thumbnail ?? '');
    }

    public function seoRobots(): string
    {
        return $this->robots ?: 'index,follow';
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
     * Check if post was imported from Google Maps
     */
    public function isImportedFromGoogleMaps(): bool
    {
        return $this->hasPlaceId();
    }
}