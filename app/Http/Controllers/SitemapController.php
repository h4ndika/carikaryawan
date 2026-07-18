<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()
            ->published()
            ->with(['category:id,slug', 'city:id,slug'])
            ->latest('published_at')
            ->limit(5000)
            ->get(['id', 'slug', 'category_id', 'city_id', 'updated_at', 'published_at']);

        $urls = [];
        $urls[] = [
            'loc' => route('home'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ];

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