<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCsrApiEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) Setting::get('csr_api_enabled', true);

        if (!$enabled) {
            return response()->json([
                'success' => false,
                'status' => 'service_disabled',
                'message' => 'Layanan API CSR saat ini dinonaktifkan oleh administrator.',
            ], 403)->header('Access-Control-Allow-Origin', '*');
        }

        return $next($request);
    }
}
