<?php

namespace App\Livewire;

use App\Models\Article;
use Livewire\Component;

class BlogShow extends Component
{
    public Article $article;

    public ?string $lightbox = null;

    public function mount(string $slug): void
    {
        $this->article = Article::published()
            ->where('slug', $slug)
            ->with(['city', 'category'])
            ->firstOrFail();

        // Increment views count safely
        $this->article->increment('views_count');
    }

    public function openLightbox(string $url): void
    {
        $this->lightbox = $url;
    }

    public function closeLightbox(): void
    {
        $this->lightbox = null;
    }

    public function render()
    {
        // Ambil data tempat yang direkomendasikan di artikel ini
        $slugs = [];
        if (preg_match_all('#https?://[^/\s]+/([^/\s]+)/([^/\s]+)/([^/\s"\'<]+)#i', $this->article->content ?? '', $matches)) {
            $slugs = array_unique($matches[3]);
        }

        $curatedPlaces = collect();
        if (!empty($slugs)) {
            $curatedPlaces = \App\Models\Post::published()
                ->whereIn('slug', $slugs)
                ->with(['category', 'city'])
                ->get();
        }

        // Jika tidak ada tempat yang ter-match dari URL, ambil rekomendasi tempat terbaik di kota artikel
        if ($curatedPlaces->isEmpty() && $this->article->city_id) {
            $curatedPlaces = \App\Models\Post::published()
                ->where('city_id', $this->article->city_id)
                ->when($this->article->category_id, fn($q) => $q->where('category_id', $this->article->category_id))
                ->orderByDesc('rating')
                ->orderByDesc('reviews_count')
                ->take(5)
                ->with(['category', 'city'])
                ->get();
        }

        $relatedArticles = Article::published()
            ->where('id', '!=', $this->article->id)
            ->when($this->article->city_id, function ($q) {
                $q->where('city_id', $this->article->city_id);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        if ($relatedArticles->count() < 3) {
            $fallback = Article::published()
                ->where('id', '!=', $this->article->id)
                ->whereNotIn('id', $relatedArticles->pluck('id'))
                ->latest('published_at')
                ->take(3 - $relatedArticles->count())
                ->get();
            $relatedArticles = $relatedArticles->concat($fallback);
        }

        return view('livewire.blog-show', [
            'curatedPlaces' => $curatedPlaces,
            'relatedArticles' => $relatedArticles,
        ])->layout('layouts.app', [
            'title' => $this->article->seoTitle() . ' - DISEKITAR',
            'metaDescription' => $this->article->seoDescription(),
            'canonicalUrl' => $this->article->url(),
            'ogImage' => $this->article->seoImage(),
            'ogType' => 'article',
        ]);
    }
}
