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
}

