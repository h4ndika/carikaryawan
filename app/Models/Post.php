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