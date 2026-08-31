<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\Home;
use App\Livewire\LandmarkShow;
use App\Livewire\PostList;
use App\Livewire\PostShow;
use App\Livewire\Search;
use Illuminate\Support\Facades\Route;

// Main routes
Route::get('/', Home::class)->name('home');
Route::get('/cari', Search::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Google AdSense ads.txt
Route::get('/ads.txt', function () {
    $content = \App\Models\Setting::get('ads_txt_content', '');
    if (empty($content)) {
        $pubId = \App\Models\Setting::get('adsense_client_id', '');
        $pubNumber = preg_replace('/[^0-9]/', '', $pubId);
        if (!empty($pubNumber)) {
            $content = "google.com, pub-{$pubNumber}, DIRECT, f08c47fec0942fa0";
        }
    }
    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('ads.txt');

// Landmark SEO routes (must be before generic /{category} routes)
Route::get('/disekitar/{landmark:slug}', LandmarkShow::class)->name('landmark.show');
Route::get('/{category}/disekitar/{landmark:slug}', LandmarkShow::class)->name('category.landmark.show');

// Category index: /hotel
Route::get('/{category}', PostList::class)
    ->name('category.home')
    ->defaults('city', null);

// Category + City listing: /hotel/bandung-kota
Route::get('/{category}/{city}', PostList::class)
    ->name('posts.list');

// Post detail: /hotel/bandung-kota/hotel-a
Route::get('/{category}/{city}/{slug}', PostShow::class)
    ->name('posts.show');