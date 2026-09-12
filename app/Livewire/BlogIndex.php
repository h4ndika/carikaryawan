<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use App\Models\City;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'kategori')]
    public string $selectedCategory = '';

    #[Url(as: 'kota')]
    public string $selectedCity = '';

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCity(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Article::published()
            ->with(['city', 'category'])
            ->latest('published_at');

        if (!empty($this->selectedCategory)) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->selectedCategory);
            });
        }

        if (!empty($this->selectedCity)) {
            $query->whereHas('city', function ($q) {
                $q->where('slug', $this->selectedCity);
            });
        }

        $articles = $query->paginate(9);

        $categories = Category::whereHas('articles', function ($q) {
            $q->published();
        })->get();

        $cities = City::whereHas('articles', function ($q) {
            $q->published();
        })->get();

        return view('livewire.blog-index', [
            'articles' => $articles,
            'categories' => $categories,
            'cities' => $cities,
        ])->layout('layouts.app', [
            'title' => 'Blog Editorial & Panduan Eksplorasi - DISEKITAR',
            'metaDescription' => 'Kurasi panduan perjalanan, rekomendasi kuliner, tempat wisata tersembunyi, dan wawasan lokal terbaik di berbagai kota di Indonesia.',
            'canonicalUrl' => route('blog.index'),
        ]);
    }
}
