<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'path',
        'url',
        'ip_hash',
        'referrer',
        'referrer_domain',
        'device',
        'viewed_date',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeDateBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('viewed_date', [$startDate, $endDate]);
    }
}
