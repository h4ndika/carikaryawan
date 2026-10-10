<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Landmark;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

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

        // 1c. Blog Index & Articles
        $urls[] = [
            'loc' => route('blog.index'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        $articles = \App\Models\Article::published()->latest('published_at')->limit(1000)->get(['id', 'slug', 'updated_at', 'published_at']);
        foreach ($articles as $art) {
            $urls[] = [
                'loc' => route('blog.show', ['slug' => $art->slug]),
                'lastmod' => optional($art->updated_at)->toAtomString() ?? optional($art->published_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 2. Category Index Pages
        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug', 'updated_at']);
        foreach ($categories as $cat) {
            $urls[] = [
                'loc' => route('category.home', ['category' => $cat->slug]),
                'lastmod' => optional($cat->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        // 4. Landmark Pages. Category+landmark combos are left out: most are thin/empty
        // and flooded Google with low-value URLs (they stay reachable via internal links).
        $landmarks = Landmark::query()->get(['id', 'slug', 'updated_at', 'latitude', 'longitude', 'default_radius_km']);
        $indexableIds = Cache::remember('sitemap_indexable_landmark_ids', 3600, function () use ($landmarks) {
            $points = Post::published()->whereNotNull('latitude')->whereNotNull('longitude')
                ->get(['latitude', 'longitude'])
                ->map(fn (Post $post) => [deg2rad((float) $post->latitude), deg2rad((float) $post->longitude)])
                ->all();

            // ponytail: bounded by current inventory; precompute counts in a scheduled command if cold builds get slow.
            $ids = [];
            foreach ($landmarks as $landmark) {
                $latitude = deg2rad((float) $landmark->latitude);
                $longitude = deg2rad((float) $landmark->longitude);
                $angularRadius = $landmark->radiusKm() / 6371;
                $sinLatitude = sin($latitude);
                $cosLatitude = cos($latitude);
                $count = 0;
                foreach ($points as [$lat, $lng]) {
                    // Latitude bound avoids most trig calls, including at poles / across the date line.
                    if (abs($lat - $latitude) > $angularRadius) {
                        continue;
                    }
                    $cosDistance = $cosLatitude * cos($lat) * cos($lng - $longitude) + $sinLatitude * sin($lat);
                    if (acos(max(-1, min(1, $cosDistance))) <= $angularRadius && ++$count >= Landmark::MIN_PLACES) {
                        $ids[] = $landmark->id;
                        break;
                    }
                }
            }

            return $ids;
        });
        foreach ($landmarks->whereIn('id', $indexableIds) as $landmark) {
            $urls[] = [
                'loc' => route('landmark.show', ['landmark' => $landmark->slug]),
                'lastmod' => optional($landmark->updated_at)->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // 5. Category + City and Post Details
        $posts = Post::query()
            ->published()
            ->with(['category:id,slug', 'city:id,slug'])
            ->latest('published_at')
            ->limit(5000)
            ->get(['id', 'slug', 'category_id', 'city_id', 'updated_at', 'published_at', 'expired_at'])
            // Closed jobs are noindex, so they must not be in the sitemap either.
            ->reject(fn (Post $post) => !$post->category || !$post->city || ($post->isJob() && $post->isExpired()));

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