<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWwwToApex
{
    public function handle(Request $request, Closure $next): Response
    {
        $url = parse_url((string) config('app.url'));
        $host = strtolower($url['host'] ?? '');
        $scheme = strtolower($url['scheme'] ?? '');

        if ($host !== '' && !str_starts_with($host, 'www.')
            && in_array($scheme, ['http', 'https'], true)
            && $request->getHost() === 'www.' . $host) {
            $origin = $scheme . '://' . $host . (isset($url['port']) ? ':' . $url['port'] : '');

            return redirect()->away($origin . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
