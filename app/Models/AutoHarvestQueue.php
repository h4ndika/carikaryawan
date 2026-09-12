<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutoHarvestQueue extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
