<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'posted_to_threads_at' => 'datetime',
        'views_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
            if ($article->status === 'published' && is_null($article->published_at)) {
                $article->published_at = now();
            }
        });

        static::updating(function ($article) {
            if ($article->status === 'published' && is_null($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function url(): string
    {
        return route('blog.show', ['slug' => $this->slug]);
    }

    public function seoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function seoDescription(): string
    {
        return $this->meta_description ?: ($this->excerpt ?: Str::limit(strip_tags($this->content), 160));
    }

    public function seoImage(): string
    {
        return $this->featured_image_url ?: asset('images/placeholder.svg');
    }

    public function readingTime(): string
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = max(1, (int) ceil($wordCount / 200));
        return "{$minutes} menit baca";
    }
}
