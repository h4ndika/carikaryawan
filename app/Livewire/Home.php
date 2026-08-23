<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('DISEKITAR — Apa saja di sekitarmu')]
class Home extends Component
{
    #[Url(as: 'category', history: true)]
    public string $selectedCategory = '';

    public string $selectedCity = '';

    public function updatedSelectedCategory(): void
    {
        // Reset or react when category changes
    }

    public function goToList(): void
    {
        $this->validate([
            'selectedCategory' => ['required', 'string'],
            'selectedCity' => ['required', 'string'],
        ], [
            'selectedCategory.required' => 'Pilih kategori dulu.',
            'selectedCity.required' => 'Pilih kota dulu.',
        ]);

        // Redirect to subdomain-based URL
        $this->redirectRoute('posts.list', [
            'category' => $this->selectedCategory,
            'city' => $this->selectedCity,
        ], navigate: true);
    }

    public function render()
    {
        $postQuery = Post::query()
            ->published()
            ->with(['category', 'city', 'province', 'images']);

        if ($this->selectedCategory !== '') {
            $postQuery->whereHas('category', fn($q) => $q->where('slug', $this->selectedCategory));
        }

        $categories = Category::query()->orderBy('name')->get();

        // Resolve slug ke nama kategori yang sebenarnya
        $activeCategoryName = '';
        if ($this->selectedCategory !== '') {
            $activeCategoryName = $categories->firstWhere('slug', $this->selectedCategory)?->name ?? ucfirst(str_replace('-', ' ', $this->selectedCategory));
        }

        return view('livewire.home', [
            'selectedCategory' => $this->selectedCategory,
            'activeCategoryName' => $activeCategoryName,
            'categories' => $categories,
            // Limit kota untuk select (full list berat). Prioritas kota yang punya post.
            'cities' => City::query()
                ->whereHas('posts', fn($q) => $q->published())
                ->orderBy('name')
                ->limit(200)
                ->get()
                ->whenEmpty(fn() => City::query()->orderBy('name')->limit(50)->get()),
            'posts' => $postQuery
                ->latest('published_at')
                ->limit(12)
                ->get(),
        ])->layout('layouts.app');
    }
}