<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Http::globalRequestMiddleware(function (\Psr\Http\Message\RequestInterface $request) {
            $uri = (string) $request->getUri();
            if (str_contains($uri, 'chat/completions')) {
                $body = (string) $request->getBody();
                $data = json_decode($body, true);
                if (is_array($data) && (!isset($data['stream']) || $data['stream'] !== false)) {
                    $data['stream'] = false;
                    $newBody = \GuzzleHttp\Psr7\Utils::streamFor(json_encode($data));
                    $request = $request->withBody($newBody);
                }
            }
            return $request;
        });
    }
}
