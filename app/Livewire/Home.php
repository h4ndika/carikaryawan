<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('DISEKITAR — Apa saja di sekitarmu')]
class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'categories' => Category::query()->orderBy('name')->get(),
            'posts' => Post::query()
                ->published()
                ->with(['category', 'city', 'province'])
                ->latest('published_at')
                ->limit(12)
                ->get(),
        ])->layout('layouts.app');
    }
}