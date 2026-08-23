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

    public function mount(string $category, ?string $city = null): void
    {
        $this->categorySlug = $category;
        $this->citySlug = $city ?? '';

        $this->category = Category::query()->where('slug', $category)->firstOrFail();
        
        if ($city) {
            $this->city = City::query()->where('slug', $city)->firstOrFail();
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
            ->paginate(12);

        $title = $this->citySlug 
            ? $this->category->name . ' di ' . $this->city->name . ' — DISEKITAR'
            : $this->category->name . ' — DISEKITAR';

        return view('livewire.post-list', [
            'posts' => $posts,
        ])
            ->layout('layouts.app')
            ->title($title);
    }
}