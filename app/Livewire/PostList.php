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

    public string $categorySlug;

    public string $citySlug;

    public Category $category;

    public City $city;

    public function mount(string $category, string $city): void
    {
        $this->categorySlug = $category;
        $this->citySlug = $city;

        $this->category = Category::query()->where('slug', $category)->firstOrFail();
        $this->city = City::query()->where('slug', $city)->firstOrFail();
    }

    public function render()
    {
        $posts = Post::query()
            ->published()
            ->where('category_id', $this->category->id)
            ->where('city_id', $this->city->id)
            ->with(['category', 'city', 'province'])
            ->latest('published_at')
            ->paginate(12);

        $title = $this->category->name . ' di ' . $this->city->name . ' — DISEKITAR';

        return view('livewire.post-list', [
            'posts' => $posts,
        ])
            ->layout('layouts.app')
            ->title($title);
    }
}