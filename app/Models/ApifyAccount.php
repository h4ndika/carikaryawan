<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApifyAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'email',
        'token',
        'status',
        'usage_count',
        'last_used_at',
        'notes',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    public function scrapeJobs(): HasMany
    {
        return $this->hasMany(GoogleMapsScrapeJob::class);
    }

    /**
     * Scope to find active accounts ordered by least used (round-robin / load-balancing)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the next rotated active account
     */
    public static function getNextActiveAccount(): ?self
    {
        return self::active()
            ->orderBy('last_used_at', 'asc')
            ->orderBy('usage_count', 'asc')
            ->first();
    }

    /**
     * Increment usage count and mark last used
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
