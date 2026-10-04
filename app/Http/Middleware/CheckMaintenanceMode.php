<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // 1. Selalu izinkan akses ke Admin Panel (rahasia), Livewire internal, dan Health check
        if ($this->isExcludedPath($path)) {
            return $next($request);
        }

        // 2. Cek status maintenance dari database Setting
        $isMaintenance = (bool) Setting::get('maintenance_mode_enabled', false);

        if (!$isMaintenance) {
            return $next($request);
        }

        // 3. Selalu izinkan user yang login sebagai admin di web guard
        if (auth('web')->check()) {
            return $next($request);
        }

        // 4. Periksa IP Whitelist jika diatur oleh admin
        $whitelist = Setting::get('maintenance_ip_whitelist', '');
        if (!empty($whitelist)) {
            $allowedIps = array_filter(array_map('trim', explode(',', $whitelist)));
            if (in_array($request->ip(), $allowedIps, true)) {
                return $next($request);
            }
        }

        $title = Setting::get('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan');
        $message = Setting::get('maintenance_message', 'Kami sedang melakukan peningkatan performa dan pemeliharaan berkala untuk memberikan pengalaman terbaik. Layanan akan segera kembali normal.');
        $estimated = Setting::get('maintenance_estimated_time', 'Segera / Dalam beberapa saat');

        // Tampilkan layout maintenance langsung di URL yang sedang diakses (HTTP 503 Service Unavailable, no redirect)
        return response()->view('maintenance', [
            'title' => $title,
            'message' => $message,
            'estimated_time' => $estimated,
        ], 503)->header('Retry-After', '3600');
    }

    protected function isExcludedPath(string $path): bool
    {
        $excluded = [
            'api',
            'rahasia',
            'livewire',
            'filament',
            'up',
            'sanctum',
            '_debugbar',
        ];

        foreach ($excluded as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}
