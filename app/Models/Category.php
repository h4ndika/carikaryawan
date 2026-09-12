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

    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class);
    }
}

