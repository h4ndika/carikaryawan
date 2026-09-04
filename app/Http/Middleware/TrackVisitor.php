<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests on html/web pages
        if ($request->method() !== 'GET' || $response->getStatusCode() >= 400) {
            return $response;
        }

        // Avoid tracking livewire requests, admin panel, api, and internal files
        $path = $request->path();
        if ($this->shouldIgnorePath($path)) {
            return $response;
        }

        // Filter out logged in administrators
        if (auth('web')->check()) {
            return $response;
        }

        $userAgent = (string) $request->userAgent();

        // Filter out search engine bots and automated crawlers
        if ($this->isBot($userAgent)) {
            return $response;
        }

        // Record page view asynchronously/safely without breaking response
        try {
            $this->recordView($request, $path, $userAgent);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    protected function shouldIgnorePath(string $path): bool
    {
        // Admin, Livewire, API, Telescope, Horizon, assets
        $ignoredPrefixes = [
            'rahasia',
            'livewire',
            'api',
            'filament',
            'up',
            'sanctum',
            '_debugbar',
        ];

        foreach ($ignoredPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        // Ignored exact paths
        $ignoredExact = [
            'sitemap.xml',
            'robots.txt',
            'ads.txt',
            'favicon.ico',
        ];

        if (in_array($path, $ignoredExact, true)) {
            return true;
        }

        // File extensions
        if (preg_match('/\.(css|js|map|png|jpg|jpeg|gif|webp|svg|woff|woff2|ttf|eot|ico|xml|txt)$/i', $path)) {
            return true;
        }

        return false;
    }

    protected function isBot(string $userAgent): bool
    {
        if (empty($userAgent)) {
            return true;
        }

        $botPatterns = [
            'bot', 'crawl', 'spider', 'slurp', 'mediapartners', 'google',
            'bingbot', 'yandex', 'baidu', 'duckduckbot', 'yahoo', 'facebookexternalhit',
            'ahrefs', 'semrush', 'petalbot', 'bytespider', 'python', 'curl', 'wget',
            'lighthouse', 'pagespeed', 'headless',
        ];

        $lowerAgent = strtolower($userAgent);
        foreach ($botPatterns as $bot) {
            if (str_contains($lowerAgent, $bot)) {
                return true;
            }
        }

        return false;
    }

    protected function recordView(Request $request, string $path, string $userAgent): void
    {
        $url = $request->fullUrl();
        $ip = $request->ip() ?: '127.0.0.1';
        $ipHash = hash('sha256', $ip . config('app.key'));
        $today = now()->toDateString();

        // Detect device
        $device = 'desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $userAgent)) {
            $device = 'tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|mobile)/i', $userAgent)) {
            $device = 'mobile';
        }

        // Detect referrer
        $referrer = $request->header('referer');
        $referrerDomain = null;
        if (!empty($referrer)) {
            $host = parse_url($referrer, PHP_URL_HOST);
            if (!empty($host)) {
                $host = preg_replace('/^www\./i', '', strtolower($host));
                $currentHost = preg_replace('/^www\./i', '', strtolower($request->getHost()));
                // If not self-referral
                if ($host !== $currentHost) {
                    $referrerDomain = $host;
                }
            }
        }

        // Check if viewing a specific post
        $postId = null;
        $route = $request->route();
        if ($route && $route->getName() === 'posts.show') {
            $postParam = $route->parameter('slug');
            if ($postParam instanceof Post) {
                $postId = $postParam->id;
            } elseif (is_string($postParam)) {
                $postId = Post::where('slug', $postParam)->value('id');
            }
        } elseif (preg_match('/^[^\/]+\/[^\/]+\/([^\/]+)$/', $path, $matches)) {
            // Fallback match: /{category}/{city}/{slug}
            $slug = $matches[1];
            $postId = Post::where('slug', $slug)->value('id');
        }

        // Insert page view
        PageView::create([
            'post_id' => $postId,
            'path' => '/' . ltrim($path, '/'),
            'url' => substr($url, 0, 500),
            'ip_hash' => $ipHash,
            'referrer' => $referrer ? substr($referrer, 0, 500) : null,
            'referrer_domain' => $referrerDomain,
            'device' => $device,
            'viewed_date' => $today,
            'created_at' => now(),
        ]);

        // Increment post views_count if viewing a post
        if ($postId) {
            Post::where('id', $postId)->increment('views_count');
        }
    }
}
