<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    public const MIN_PLACES = 3;

    public function radiusKm(): float
    {
        return (float) ($this->default_radius_km ?: 3);
    }

    protected $guarded = [
        'id',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
