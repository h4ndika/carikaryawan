<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use App\Livewire\PostList;
use App\Livewire\PostShow;
use App\Livewire\Search;
use Illuminate\Support\Facades\Route;

// Main domain routes (disekitar.test)
Route::domain(config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'disekitar.test')->group(function () {
    Route::get('/', Home::class)->name('home');
    Route::get('/cari', Search::class)->name('search');
    Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
});

// Category subdomain routes ({category}.disekitar.test)
Route::domain('{category}.' . (config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'disekitar.test'))
    ->where(['category' => '[a-z0-9\-]+'])
    ->group(function () {
        // Category homepage: hotel.disekitar.test/
        Route::get('/', PostList::class)
            ->name('category.home')
            ->defaults('city', null);

        // City listing: hotel.disekitar.test/bandung-kota
        Route::get('/{city}', PostList::class)
            ->name('posts.list')
            ->where('city', '[a-z0-9\-]+');

        // Post detail: hotel.disekitar.test/bandung-kota/hotel-a
        Route::get('/{city}/{slug}', PostShow::class)
            ->name('posts.show')
            ->where([
                'city' => '[a-z0-9\-]+',
                'slug' => '[a-z0-9\-]+',
            ]);
    });