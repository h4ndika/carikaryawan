<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use Livewire\Component;

class PostShow extends Component
{
    public string $categorySlug;

    public string $citySlug;

    public string $slug;

    public Post $post;

    public ?string $lightbox = null;

    public function mount(string $category, string $city, string $slug): void
    {
        $this->categorySlug = $category;
        $this->citySlug = $city;
        $this->slug = $slug;

        $categoryModel = Category::query()->where('slug', $category)->firstOrFail();
        $cityModel = City::query()->where('slug', $city)->firstOrFail();

        $this->post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->where('category_id', $categoryModel->id)
            ->where('city_id', $cityModel->id)
            ->with(['category', 'city', 'province', 'details', 'images'])
            ->firstOrFail();
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
        $canonical = $this->post->canonical_url
            ?: route('posts.show', [
                'category' => $this->post->category->slug,
                'city' => $this->post->city->slug,
                'slug' => $this->post->slug,
            ]);

        return view('livewire.post-show')
            ->layout('layouts.app', [
                'metaDescription' => $this->post->seoDescription(),
                'metaRobots' => $this->post->seoRobots(),
                'canonicalUrl' => $canonical,
                'ogImage' => $this->post->seoImage() ?: null,
            ])
            ->title($this->post->seoTitle() . ' — DISEKITAR');
    }
}