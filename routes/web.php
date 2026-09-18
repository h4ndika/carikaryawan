<?php

use App\Http\Controllers\SitemapController;
use App\Livewire\BlogIndex;
use App\Livewire\BlogShow;
use App\Livewire\Home;
use App\Livewire\LandmarkShow;
use App\Livewire\PageShow;
use App\Livewire\PostList;
use App\Livewire\PostShow;
use App\Livewire\Search;
use Illuminate\Support\Facades\Route;

// Main routes
Route::get('/', Home::class)->name('home');
Route::get('/cari', Search::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

// Blog Editorial routes
Route::get('/blog', BlogIndex::class)->name('blog.index');
Route::get('/blog/{slug}', BlogShow::class)->name('blog.show');

// Mandatory Legal & Editorial Policy Routes
Route::get('/tentang-kami', PageShow::class)->name('page.about')->defaults('page', 'tentang-kami');
Route::get('/kebijakan-privasi', PageShow::class)->name('page.privacy')->defaults('page', 'kebijakan-privasi');
Route::get('/syarat-ketentuan', PageShow::class)->name('page.terms')->defaults('page', 'syarat-ketentuan');
Route::get('/kontak', PageShow::class)->name('page.contact')->defaults('page', 'kontak');
Route::get('/disclaimer', PageShow::class)->name('page.disclaimer')->defaults('page', 'disclaimer');

// English & Common URL Aliases (for Google Reviewer / External Crawlers)
Route::redirect('/about', '/tentang-kami');
Route::redirect('/about-us', '/tentang-kami');
Route::redirect('/privacy', '/kebijakan-privasi');
Route::redirect('/privacy-policy', '/kebijakan-privasi');
Route::redirect('/terms', '/syarat-ketentuan');
Route::redirect('/terms-of-service', '/syarat-ketentuan');
Route::redirect('/contact', '/kontak');
Route::redirect('/contact-us', '/kontak');

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

// Embeddable Widgets & Badges (Featured on DISEKITAR)
Route::get('/widget/post/{slug}', [\App\Http\Controllers\WidgetController::class, 'postWidget'])->name('widget.post');
Route::get('/widget/badge/{slug}', [\App\Http\Controllers\WidgetController::class, 'badgeWidget'])->name('widget.badge');

// Dynamic Job Cover Image (for OpenGraph & Hero Banner)
Route::get('/job-image/{slug}.svg', [\App\Http\Controllers\JobImageController::class, 'show'])->name('job.image');
Route::get('/job-image/{slug}.png', [\App\Http\Controllers\JobImageController::class, 'png'])->name('job.image.png');

// Full-page ATS CV & Resume Builder
Route::get('/karir/cv-builder', \App\Livewire\CvBuilder::class)
    ->name('career.cv-builder');
Route::post('/job-tools/polish-cv', [\App\Http\Controllers\CvBuilderApiController::class, 'polish'])
    ->middleware('throttle:30,1')
    ->name('career.cv-builder.polish');

// 1-Click AI Cover Letter Generator for Jobs
Route::post('/job-tools/generate-cover-letter', [\App\Http\Controllers\JobCoverLetterController::class, 'generate'])
    ->middleware('throttle:20,1')
    ->name('job.cover-letter.generate');

// Full-page Interview Prep AI
Route::get('/karir/interview-prep', \App\Livewire\InterviewPrep::class)
    ->name('career.interview-prep');
Route::post('/job-tools/generate-interview-questions', [\App\Http\Controllers\InterviewPrepController::class, 'generate'])
    ->middleware('throttle:15,1')
    ->name('career.interview-prep.generate');


// 1-Click AI ATS CV Maker for Jobs
Route::post('/job-tools/generate-cv', [\App\Http\Controllers\JobCvController::class, 'generate'])
    ->middleware('throttle:20,1')
    ->name('job.cv.generate');

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