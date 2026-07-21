<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use App\Livewire\PostList;
use App\Livewire\PostShow;
use App\Livewire\Search;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('/cari', Search::class)->name('search');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/{category}/{city}/{slug}', PostShow::class)
    ->name('posts.show')
    ->where([
        'category' => '[a-z0-9\-]+',
        'city' => '[a-z0-9\-]+',
        'slug' => '[a-z0-9\-]+',
    ]);

Route::get('/{category}/{city}', PostList::class)
    ->name('posts.list')
    ->where([
        'category' => '[a-z0-9\-]+',
        'city' => '[a-z0-9\-]+',
    ]);