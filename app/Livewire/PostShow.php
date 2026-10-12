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

    public ?Post $post = null;

    public function mount(
        Category|string|null $category = null,
        City|string|null $city = null,
        Post|string|null $slug = null
    ): void {
        if ($category instanceof Category) {
            $categoryModel = $category;
            $this->categorySlug = $category->slug;
        } else {
            $categorySlug = $category ?: (string) (request()->route('category') ?? '');
            $this->categorySlug = $categorySlug;
            $categoryModel = Category::query()->where('slug', $categorySlug)->firstOrFail();
        }

        // Only job categories are public.
        abort_unless($categoryModel->isJob(), 404);

        if ($city instanceof City) {
            $cityModel = $city;
            $this->citySlug = $city->slug;
        } else {
            $citySlug = $city ?: (string) (request()->route('city') ?? '');
            $this->citySlug = $citySlug;
            $cityModel = City::query()->where('slug', $citySlug)->firstOrFail();
        }

        if ($slug instanceof Post) {
            $this->post = $slug;
            $this->slug = $slug->slug;
        } else {
            $postSlug = $slug ?: (string) (request()->route('slug') ?? '');
            $this->slug = $postSlug;
            $this->post = Post::query()
                ->published()
                ->where('slug', $postSlug)
                ->where('category_id', $categoryModel->id)
                ->where('city_id', $cityModel->id)
                ->with(['category', 'city', 'province', 'details', 'images'])
                ->firstOrFail();
        }
    }

    public function render()
    {
        $canonical = $this->post->canonical_url ?: $this->post->url();

        $relatedQuery = Post::query()
            ->published()
            ->where('category_id', $this->post->category_id)
            ->where('city_id', $this->post->city_id)
            ->where('id', '!=', $this->post->id)
            ->with(['category', 'city', 'province', 'images']);

        if ($this->post->isJob()) {
            $relatedQuery->activeJob();
        }

        $related = $relatedQuery
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('livewire.post-show', [
            'related' => $related,
        ])
            ->layout('layouts.app', [
                'metaDescription' => $this->post->seoDescription(),
                'metaRobots' => $this->post->seoRobots(),
                'canonicalUrl' => $canonical,
                'ogImage' => $this->post->seoImage() ?: null,
                'ogType' => 'article',
                'jsonLd' => $this->post->generateJsonLd($canonical),
            ])
            ->title($this->post->seoTitle() . ' — CariKaryawan');
    }
}