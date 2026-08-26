<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class PostList extends Component
{
    use WithPagination;

    public string $categorySlug = '';

    public string $citySlug = '';

    public ?Category $category = null;

    public ?City $city = null;

    public function mount(Category|string|null $category = null, City|string|null $city = null): void
    {
        if ($category instanceof Category) {
            $this->category = $category;
            $this->categorySlug = $category->slug;
        } else {
            $categorySlug = $category ?: (string) (request()->route('category') ?? '');
            $this->categorySlug = $categorySlug;
            $this->category = Category::query()->where('slug', $categorySlug)->firstOrFail();
        }

        if ($city instanceof City) {
            $this->city = $city;
            $this->citySlug = $city->slug;
        } else {
            $citySlug = $city ?: (string) (request()->route('city') ?? '');
            $this->citySlug = $citySlug;
            if ($this->citySlug) {
                $this->city = City::query()->where('slug', $citySlug)->firstOrFail();
            }
        }
    }

    public function render()
    {
        $postQuery = Post::query()
            ->published()
            ->where('category_id', $this->category->id);

        if ($this->citySlug) {
            $postQuery->where('city_id', $this->city->id);
        }

        $posts = $postQuery
            ->with(['category', 'city', 'province', 'images'])
            ->latest('published_at')
            ->paginate(10);

        $cities = City::query()
            ->whereHas('posts', fn ($q) => $q->published()->where('category_id', $this->category->id))
            ->orderBy('name')
            ->get();

        $editorPicks = Post::query()
            ->published()
            ->where('category_id', $this->category->id)
            ->with(['category', 'city', 'province', 'images'])
            ->latest('published_at')
            ->limit(4)
            ->get();

        $title = $this->citySlug 
            ? $this->category->name . ' di ' . $this->city->name . ' — DISEKITAR'
            : $this->category->name . ' — DISEKITAR';

        $metaDescription = $this->citySlug
            ? "Temukan kurasi rekomendasi {$this->category->name} terbaik dan terpopuler di {$this->city->name}. Ulasan lengkap, alamat, rating, dan info terdekat."
            : "Kumpulan kurasi editorial {$this->category->name} terbaik di berbagai kota di Indonesia. Temukan pilihan menarik dan terpercaya di sekitarmu.";

        $canonicalUrl = $this->citySlug
            ? route('posts.list', ['category' => $this->category->slug, 'city' => $this->city->slug])
            : route('category.home', ['category' => $this->category->slug]);

        $firstPostImg = $posts->first()?->primary_image_url ?? $editorPicks->first()?->primary_image_url;

        // JSON-LD Schema
        $siteUrl = url('/');
        $breadcrumbs = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => $siteUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $this->category->name,
                'item' => route('category.home', ['category' => $this->category->slug]),
            ],
        ];

        if ($this->citySlug && $this->city) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $this->city->name,
                'item' => $canonicalUrl,
            ];
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'CollectionPage',
                    '@id' => $canonicalUrl . '#collection',
                    'name' => $title,
                    'description' => $metaDescription,
                    'url' => $canonicalUrl,
                    'inLanguage' => 'id-ID',
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonicalUrl . '#breadcrumb',
                    'itemListElement' => $breadcrumbs,
                ],
            ],
        ];

        return view('livewire.post-list', [
            'posts' => $posts,
            'cities' => $cities,
            'editorPicks' => $editorPicks,
        ])
            ->layout('layouts.app', [
                'metaDescription' => $metaDescription,
                'canonicalUrl' => $canonicalUrl,
                'ogImage' => $firstPostImg ?: null,
                'jsonLd' => $jsonLd,
            ])
            ->title($title);
    }
}