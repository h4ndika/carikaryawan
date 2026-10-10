<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Landmark;
use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class LandmarkShow extends Component
{
    use WithPagination;

    public Landmark $landmark;

    public ?string $categorySlug = null;

    public ?Category $selectedCategory = null;

    protected $queryString = [
        'categorySlug' => ['except' => '', 'as' => 'kategori'],
    ];

    public function mount(Landmark|string $landmark, Category|string|null $category = null): void
    {
        if ($landmark instanceof Landmark) {
            $this->landmark = $landmark->load(['city', 'province']);
        } else {
            $this->landmark = Landmark::query()
                ->where('slug', (string) $landmark)
                ->with(['city', 'province'])
                ->firstOrFail();
        }

        // Check category from route if any
        $routeCategory = $category ?: request()->route('category');
        if ($routeCategory) {
            $this->categorySlug = is_string($routeCategory) ? $routeCategory : $routeCategory->slug;
        }

        if ($this->categorySlug) {
            $this->selectedCategory = Category::query()->where('slug', $this->categorySlug)->first();
        }
    }

    public function filterCategory(?string $slug = null): void
    {
        $this->categorySlug = $slug ?: null;
        $this->selectedCategory = $slug ? Category::query()->where('slug', $slug)->first() : null;
        $this->resetPage();
    }

    public function updatingCategorySlug(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $postQuery = Post::query()
            ->published()
            ->withinDistanceOf(
                (float) $this->landmark->latitude,
                (float) $this->landmark->longitude,
                (float) ($this->landmark->default_radius_km ?: 3)
            )
            ->with(['category', 'city', 'province', 'images']);

        if ($this->selectedCategory) {
            $postQuery->where('category_id', $this->selectedCategory->id);
        }

        $posts = $postQuery->paginate(10);

        $categories = Category::query()->orderBy('name')->get();

        $otherLandmarks = Landmark::query()
            ->where('city_id', $this->landmark->city_id)
            ->where('id', '!=', $this->landmark->id)
            ->limit(6)
            ->get();

        // SEO Meta setup
        $landmarkName = $this->landmark->name;
        $cityName = $this->landmark->city?->name ?? '';
        $radius = round((float) ($this->landmark->default_radius_km ?: 3), 1);

        if ($this->selectedCategory) {
            $pageTitle = "{$this->selectedCategory->name} di Sekitar {$landmarkName} ({$cityName}) — DISEKITAR";
            $metaDescription = "Daftar rekomendasi {$this->selectedCategory->name} terbaik dalam radius {$radius} km di dekat {$landmarkName}, {$cityName}. Lengkap dengan ulasan, alamat, dan jarak terdekat.";
        } else {
            $pageTitle = "Rekomendasi Tempat Menarik di Sekitar {$landmarkName} ({$cityName}) — DISEKITAR";
            $metaDescription = $this->landmark->meta_description ?: "Temukan berbagai rekomendasi hotel, restoran, kafe, dan tempat populer dalam radius {$radius} km di sekitar {$landmarkName}, {$cityName}.";
        }

        $canonical = $this->selectedCategory
            ? route('category.landmark.show', ['category' => $this->selectedCategory->slug, 'landmark' => $this->landmark->slug])
            : route('landmark.show', ['landmark' => $this->landmark->slug]);

        // JSON-LD Schema
        $siteUrl = url('/');
        $breadcrumbs = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Beranda',
                'item' => $siteUrl,
            ],
        ];

        $pos = 2;
        if ($this->selectedCategory) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => $this->selectedCategory->name,
                'item' => route('category.home', ['category' => $this->selectedCategory->slug]),
            ];
        }

        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $pos,
            'name' => "Di Sekitar {$landmarkName}",
            'item' => $canonical,
        ];

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'TouristAttraction',
                    '@id' => $canonical . '#landmark',
                    'name' => $landmarkName,
                    'description' => $metaDescription,
                    'url' => $canonical,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'addressLocality' => $cityName,
                        'addressRegion' => $this->landmark->province?->name ?? '',
                        'addressCountry' => 'ID',
                    ],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => (float) $this->landmark->latitude,
                        'longitude' => (float) $this->landmark->longitude,
                    ],
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => $canonical . '#breadcrumb',
                    'itemListElement' => $breadcrumbs,
                ],
            ],
        ];

        return view('livewire.landmark-show', [
            'posts' => $posts,
            'categories' => $categories,
            'otherLandmarks' => $otherLandmarks,
            'radius' => $radius,
        ])
            ->layout('layouts.app', [
                'metaDescription' => $metaDescription,
                // Landmark pages with almost no places are thin content; keep them out of the index.
                'metaRobots' => $posts->total() >= 3 ? 'index,follow' : 'noindex,follow',
                'canonicalUrl' => $canonical,
                'jsonLd' => $jsonLd,
            ])
            ->title($pageTitle);
    }
}
