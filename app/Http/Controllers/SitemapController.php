<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private const MIN_LISTING_POSTS = 3;

    public function __invoke(): Response
    {
        $urls = [];

        // 1. Homepage
        $urls[] = [
            'loc' => route('home'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

        // 1b. Mandatory Policy & Legal Pages
        $policyRoutes = ['page.about', 'page.privacy', 'page.terms', 'page.contact', 'page.disclaimer'];
        foreach ($policyRoutes as $routeName) {
            $urls[] = [
                'loc' => route($routeName),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // 1c. Career tools
        foreach (['tools.hub', 'career.cv-builder', 'career.interview-prep'] as $routeName) {
            $urls[] = [
                'loc' => route($routeName),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        // 2. Job Category Index Pages
        $categories = Category::query()->jobs()->orderBy('name')->get(['id', 'name', 'slug', 'updated_at']);
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => route('category.home', ['category' => $cat->slug]),
                'lastmod' => optional($cat->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        // 3. Category + City and Job Details
        $posts = Post::query()
            ->published()
            ->jobs()
            ->with(['category:id,name,slug', 'city:id,slug'])
            ->latest('published_at')
            ->limit(5000)
            ->get(['id', 'slug', 'category_id', 'city_id', 'updated_at', 'published_at', 'expired_at'])
            // Closed jobs are noindex, so they must not be in the sitemap either.
            ->reject(fn (Post $post) => !$post->category || !$post->city || $post->isExpired());

        // Only list category+city pages with enough posts to be worth indexing.
        // ponytail: counted within the 5000-post sample; switch to a GROUP BY query if the sitemap is split.
        $listCounts = $posts->countBy(fn (Post $post) => $post->category->slug . '|' . $post->city->slug);

        $listKeys = [];
        foreach ($posts as $post) {
            $listKey = $post->category->slug . '|' . $post->city->slug;
            if (!isset($listKeys[$listKey]) && $listCounts[$listKey] >= self::MIN_LISTING_POSTS) {
                $listKeys[$listKey] = true;
                $urls[] = [
                    'loc' => route('posts.list', [
                        'category' => $post->category->slug,
                        'city' => $post->city->slug,
                    ]),
                    'lastmod' => optional($post->updated_at)->toAtomString() ?? now()->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.8',
                ];
            }

            $urls[] = [
                'loc' => route('posts.show', [
                    'category' => $post->category->slug,
                    'city' => $post->city->slug,
                    'slug' => $post->slug,
                ]),
                'lastmod' => optional($post->updated_at)->toAtomString()
                    ?? optional($post->published_at)->toAtomString()
                    ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>' . e($url['loc']) . '</loc>';
            $lines[] = '    <lastmod>' . e($url['lastmod']) . '</lastmod>';
            $lines[] = '    <changefreq>' . e($url['changefreq']) . '</changefreq>';
            $lines[] = '    <priority>' . e($url['priority']) . '</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'application/xml');
    }
}