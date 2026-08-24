<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
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

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function landmarks()
    {
        return $this->hasMany(Landmark::class);
    }
}
