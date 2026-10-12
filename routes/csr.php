<?php

use App\Http\Controllers\Csr\CsrJobApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CSR (Client-Side Rendering) Headless API Routes
|--------------------------------------------------------------------------
| Route terisolasi khusus melayani frontend CSR mandiri (Vercel, GitHub Pages,
| Cloudflare Pages, Blogspot, dll) tanpa mengganggu routing/MVC CariKaryawan.
|
| Prefix: /api/csr/...
| Response: Pure JSON (CORS open)
*/

Route::prefix('csr')
    ->middleware([\App\Http\Middleware\EnsureCsrApiEnabled::class])
    ->group(function () {
        Route::get('/jobs', [CsrJobApiController::class, 'index'])->name('api.csr.jobs.index');
        Route::get('/jobs/{id_or_slug}', [CsrJobApiController::class, 'show'])->name('api.csr.jobs.show');
        Route::get('/config', [CsrJobApiController::class, 'config'])->name('api.csr.config');
        Route::get('/sitemap.xml', [CsrJobApiController::class, 'sitemap'])->name('api.csr.sitemap');
    });
