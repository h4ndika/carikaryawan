<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Cari Lowongan — CariKaryawan')]
class Search extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $q = '';

    #[Url(as: 'category', history: true)]
    public string $category = '';

    #[Url(as: 'city', history: true)]
    public string $city = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedCity(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset('q', 'category', 'city');
        $this->resetPage();
    }

    public function render()
    {
        $query = Post::query()
            ->published()
            ->jobs()
            ->with(['category', 'city', 'province', 'images', 'details']);

        $term = trim($this->q);
        if ($term !== '') {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content', 'like', $like);
            });
        }

        if ($this->category !== '') {
            $query->whereHas('category', fn($q) => $q->where('slug', $this->category));
        }

        if ($this->city !== '') {
            $query->whereHas('city', fn($q) => $q->where('slug', $this->city));
        }

        return view('livewire.search', [
            'posts' => $query->latest('published_at')->paginate(12),
            'categories' => Category::query()->jobs()->orderBy('name')->get(['id', 'name', 'slug']),
            'cities' => City::query()->orderBy('name')->get(['id', 'name', 'slug']),
        ])->layout('layouts.app', [
                    'metaDescription' => 'Cari lowongan kerja berdasarkan posisi, kategori, dan kota di seluruh Indonesia.',
                    'metaRobots' => 'noindex,follow',
                ]);
    }
}