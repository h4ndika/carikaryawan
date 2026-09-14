<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class JobImageController extends Controller
{
    /**
     * Generate a crisp, branded SVG cover banner for job postings.
     * Dimensions: 1200 x 630 (Perfect for OpenGraph, Twitter Card, and On-Page Hero).
     */
    public function show(string $slug): Response
    {
        $post = Post::with(['category', 'city', 'province'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $svg = $this->generateSvg($post);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Render job cover banner as PNG (for OpenGraph & social media link previews).
     */
    public function png(string $slug): Response
    {
        $cachePath = storage_path("app/public/job-banners/{$slug}.png");

        if (file_exists($cachePath) && filesize($cachePath) > 1000) {
            return response(file_get_contents($cachePath), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        $post = Post::with(['category', 'city', 'province'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $svg = $this->generateSvg($post);

        // Convert SVG to PNG using rsvg-convert CLI if available
        $png = $this->convertSvgToPng($svg);

        if ($png) {
            // Save to disk cache
            if (!is_dir(dirname($cachePath))) {
                @mkdir(dirname($cachePath), 0755, true);
            }
            @file_put_contents($cachePath, $png);

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800, immutable',
            ]);
        }

        // Fallback: Redirect to PNG logo icon if converter is unavailable
        return redirect(asset('android-chrome-512x512.png'), 302);
    }

    private function convertSvgToPng(string $svg): ?string
    {
        $bin = '/usr/bin/rsvg-convert';
        if (!file_exists($bin)) {
            $bin = trim((string) shell_exec('which rsvg-convert 2>/dev/null'));
        }

        if (empty($bin) || !is_executable($bin)) {
            return null;
        }

        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open("{$bin} -f png -w 1200 -h 630", $descriptors, $pipes);

        if (is_resource($process)) {
            fwrite($pipes[0], $svg);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            fclose($pipes[2]);
            $returnCode = proc_close($process);

            if ($returnCode === 0 && !empty($stdout)) {
                return $stdout;
            }
        }

        return null;
    }

    public function generateSvg(Post $post): string
    {

        $rawTitle = $post->title;
        $company = '';
        $role = $rawTitle;

        // Parse "Role - Company" or "Role di Company"
        if (str_contains($rawTitle, ' - ')) {
            $parts = explode(' - ', $rawTitle, 2);
            $role = trim($parts[0]);
            $company = trim($parts[1]);
        } elseif (preg_match('/^(.+?)\s+(?:di|at)\s+(.+)$/i', $rawTitle, $matches)) {
            $role = trim($matches[1]);
            $company = trim($matches[2]);
        }

        $location = ($post->city?->name ?? 'Indonesia') . ($post->province ? ', ' . $post->province->name : '');
        $categoryName = strtoupper($post->category?->name ?? 'LOWONGAN KERJA');

        // Text wrapping for SVG
        $roleLines = $this->wrapText($role, 32, 2);
        $companyEscaped = htmlspecialchars(Str::limit($company ?: 'Perusahaan Rekanan', 45), ENT_XML1, 'UTF-8');
        $locationEscaped = htmlspecialchars(Str::limit($location, 40), ENT_XML1, 'UTF-8');
        $categoryEscaped = htmlspecialchars($categoryName, ENT_XML1, 'UTF-8');

        $roleSvgLines = '';
        $baseY = count($roleLines) === 1 ? 300 : 275;
        foreach ($roleLines as $idx => $line) {
            $y = $baseY + ($idx * 52);
            $escaped = htmlspecialchars($line, ENT_XML1, 'UTF-8');
            $roleSvgLines .= "<text x=\"90\" y=\"{$y}\" font-family=\"'Plus Jakarta Sans', system-ui, -apple-system, sans-serif\" font-size=\"42\" font-weight=\"800\" fill=\"#FFFFFF\" letter-spacing=\"-0.5\">{$escaped}</text>\n";
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630">
    <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#09090b" />
            <stop offset="50%" stop-color="#18181b" />
            <stop offset="100%" stop-color="#27272a" />
        </linearGradient>

        <linearGradient id="cardGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.07" />
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0.02" />
        </linearGradient>

        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#ffffff" stroke-opacity="0.04" stroke-width="1"/>
        </pattern>
    </defs>

    <rect width="1200" height="630" fill="url(#bgGrad)" />
    <rect width="1200" height="630" fill="url(#grid)" />

    <circle cx="1080" cy="120" r="280" fill="#d97706" opacity="0.12" />
    <circle cx="120" cy="540" r="220" fill="#b45309" opacity="0.08" />

    <rect x="50" y="50" width="1100" height="530" rx="28" fill="url(#cardGrad)" stroke="#ffffff" stroke-opacity="0.12" stroke-width="1.5" />

    <g transform="translate(90, 105)">
        <rect x="0" y="0" width="220" height="40" rx="20" fill="#f59e0b" fill-opacity="0.18" stroke="#f59e0b" stroke-opacity="0.5" stroke-width="1" />
        <path d="M18 16 H26 V13 A2 2 0 0 1 28 11 H36 A2 2 0 0 1 38 13 V16 H46 A2 2 0 0 1 48 18 V29 A2 2 0 0 1 46 31 H18 A2 2 0 0 1 16 29 V18 A2 2 0 0 1 18 16 Z M28 14 V16 H36 V14 Z" fill="#fbbf24" transform="translate(6, 0) scale(0.9)" />
        <text x="56" y="25" font-family="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif" font-size="13" font-weight="700" fill="#fbbf24" letter-spacing="1">{$categoryEscaped}</text>
    </g>

    <g transform="translate(970, 105)">
        <text x="140" y="27" font-family="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif" font-size="20" font-weight="900" fill="#FFFFFF" text-anchor="end" letter-spacing="2">DISEKITAR<tspan fill="#f59e0b">.</tspan></text>
    </g>

    <g>
        {$roleSvgLines}
    </g>

    <line x1="90" y1="395" x2="1110" y2="395" stroke="#ffffff" stroke-opacity="0.1" stroke-width="1" />

    <g transform="translate(90, 445)">
        <g fill="#a1a1aa">
            <path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M4 21h16M9 8h2M9 12h2M9 16h2M14 8h-2M14 12h-2M14 16h-2" stroke="#a1a1aa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" transform="scale(1.2)" />
        </g>
        <text x="36" y="20" font-family="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif" font-size="22" font-weight="600" fill="#e4e4e7">{$companyEscaped}</text>

        <g transform="translate(480, 0)">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#f59e0b" transform="scale(1.1)" />
            <text x="32" y="20" font-family="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif" font-size="20" font-weight="500" fill="#a1a1aa">{$locationEscaped}</text>
        </g>
    </g>

    <g transform="translate(90, 520)">
        <circle cx="6" cy="6" r="4" fill="#22c55e" />
        <text x="20" y="10" font-family="'Plus Jakarta Sans', system-ui, -apple-system, sans-serif" font-size="13" font-weight="600" fill="#a1a1aa">Kurasi Lowongan Kerja Terverifikasi · disekitar.com</text>
    </g>
</svg>
SVG;

        return $svg;
    }

    private function wrapText(string $text, int $maxCharsPerLine, int $maxLines): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (empty($currentLine)) {
                $currentLine = $word;
            } elseif (mb_strlen($currentLine . ' ' . $word) <= $maxCharsPerLine) {
                $currentLine .= ' ' . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
                if (count($lines) >= $maxLines - 1) {
                    break;
                }
            }
        }

        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }

        if (count($lines) >= $maxLines && count($words) > count(explode(' ', implode(' ', $lines)))) {
            $lines[$maxLines - 1] = Str::limit($lines[$maxLines - 1], $maxCharsPerLine, '...');
        }

        return array_slice($lines, 0, $maxLines);
    }
}