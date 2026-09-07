<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleMapsScrapeJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'engine',
        'query',
        'location',
        'category_id',
        'city_id',
        'target_limit',
        'include_images',
        'include_reviews',
        'publish_imported',
        'apify_account_id',
        'apify_run_id',
        'apify_dataset_id',
        'status',
        'total_scraped',
        'total_imported',
        'total_updated',
        'total_skipped',
        'total_failed',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'target_limit' => 'integer',
        'include_images' => 'boolean',
        'include_reviews' => 'boolean',
        'publish_imported' => 'boolean',
        'total_scraped' => 'integer',
        'total_imported' => 'integer',
        'total_updated' => 'integer',
        'total_skipped' => 'integer',
        'total_failed' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function apifyAccount(): BelongsTo
    {
        return $this->belongsTo(ApifyAccount::class);
    }
}
