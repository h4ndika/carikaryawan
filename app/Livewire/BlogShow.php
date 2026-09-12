<?php

namespace App\Livewire;

use App\Models\Article;
use Livewire\Component;

class BlogShow extends Component
{
    public Article $article;

    public function mount(string $slug): void
    {
        $this->article = Article::published()
            ->where('slug', $slug)
            ->with(['city', 'category'])
            ->firstOrFail();

        // Increment views count safely
        $this->article->increment('views_count');
    }

    public function render()
    {
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
