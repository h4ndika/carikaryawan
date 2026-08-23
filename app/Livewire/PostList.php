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