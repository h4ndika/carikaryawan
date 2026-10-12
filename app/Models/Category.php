<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get subdomain category homepage URL
     */
    public function url(): string
    {
        return route('category.home', ['category' => $this->slug]);
    }

    /**
     * Determine if this category is a job category.
     */
    public function isJob(): bool
    {
        $slug = strtolower($this->slug ?? '');
        $name = strtolower($this->name ?? '');

        return str_contains($slug, 'lowongan')
            || str_contains($name, 'lowongan')
            || str_contains($slug, 'loker')
            || str_contains($name, 'loker');
    }

    /**
     * Limit to job categories (same case-insensitive lowongan/loker substring rule as isJob()).
     */
    public function scopeJobs($query)
    {
        return $query->where(function ($q) {
            foreach (['slug', 'name'] as $col) {
                $q->orWhereRaw("LOWER({$col}) LIKE ?", ['%lowongan%'])
                  ->orWhereRaw("LOWER({$col}) LIKE ?", ['%loker%']);
            }
        });
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }
}

