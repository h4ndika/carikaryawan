<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Landmark;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
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

        // 2. Search Page
        $urls[] = [
            'loc' => route('search'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.5',
        ];

        // 3. Category Index Pages
        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug', 'updated_at']);
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => route('category.home', ['category' => $cat->slug]),
                'lastmod' => optional($cat->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        // 4. Landmark Pages & Category + Landmark
        $landmarks = Landmark::query()->with('city:id,slug')->get(['id', 'slug', 'city_id', 'updated_at']);
        foreach ($landmarks as $landmark) {
            $urls[] = [
                'loc' => route('landmark.show', ['landmark' => $landmark->slug]),
                'lastmod' => optional($landmark->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];

            foreach ($categories as $cat) {
                $urls[] = [
                    'loc' => route('category.landmark.show', ['category' => $cat->slug, 'landmark' => $landmark->slug]),
                    'lastmod' => optional($landmark->updated_at)->toAtomString() ?? now()->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        }

        // 5. Category + City and Post Details
        $posts = Post::query()
            ->published()
            ->with(['category:id,slug', 'city:id,slug'])
            ->latest('published_at')
            ->limit(5000)
            ->get(['id', 'slug', 'category_id', 'city_id', 'updated_at', 'published_at']);

        $listKeys = [];
        foreach ($posts as $post) {
            if (!$post->category || !$post->city) {
                continue;
            }

            $listKey = $post->category->slug . '|' . $post->city->slug;
            if (!isset($listKeys[$listKey])) {
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