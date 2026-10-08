<?php

namespace App\Http\Controllers\Csr;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CsrJobApiController extends Controller
{
    /**
     * Helper to retrieve Job category IDs without affecting existing system.
     */
    protected function getJobCategoryIds(): array
    {
        return Cache::remember('csr_job_category_ids', 86400, function () {
            return Category::where('slug', 'lowongan-kerja')
                ->orWhere('name', 'LIKE', '%Lowongan%')
                ->orWhere('name', 'LIKE', '%Loker%')
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * Get Paginated list of jobs for CSR frontend.
     * GET /api/csr/jobs?q=admin&city=jakarta&limit=15&page=1
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $city = trim((string) $request->input('city', ''));
        $limit = min(max((int) $request->input('limit', 12), 1), 50);
        $page = max((int) $request->input('page', 1), 1);

        $cacheKey = "csr_jobs_list_" . md5("{$q}_{$city}_{$limit}_{$page}");

        $response = Cache::remember($cacheKey, 600, function () use ($q, $city, $limit) {
            $catIds = $this->getJobCategoryIds();

            $query = Post::query()
                ->where('status', 'published')
                ->where(function ($sub) use ($catIds) {
                    if (!empty($catIds)) {
                        $sub->whereIn('category_id', $catIds);
                    } else {
                        $sub->where('title', 'LIKE', '%lowongan%')
                            ->orWhere('title', 'LIKE', '%loker%');
                    }
                })
                ->with(['city:id,name,slug', 'province:id,name', 'category:id,name,slug', 'details'])
                ->latest('published_at');

            if ($q !== '') {
                $query->where(function ($sq) use ($q) {
                    $sq->where('title', 'LIKE', "%{$q}%")
                       ->orWhere('content', 'LIKE', "%{$q}%");
                });
            }

            if ($city !== '') {
                $query->whereHas('city', function ($cq) use ($city) {
                    $cq->where('name', 'LIKE', "%{$city}%")
                       ->orWhere('slug', 'LIKE', "%{$city}%");
                });
            }

            $paginator = $query->paginate($limit);

            $items = collect($paginator->items())->map(function ($post) {
                return $this->formatJobSummary($post);
            });

            return [
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more' => $paginator->hasMorePages(),
                ],
            ];
        });

        return response()->json($response)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
    }

    /**
     * Get detail of a specific job by slug or ID.
     * GET /api/csr/jobs/{slug_or_id}
     */
    public function show(string $identifier): JsonResponse
    {
        $cacheKey = "csr_job_detail_" . md5($identifier);

        $response = Cache::remember($cacheKey, 1800, function () use ($identifier) {
            $query = Post::query()
                ->where('status', 'published')
                ->with(['city:id,name,slug', 'province:id,name', 'category:id,name,slug', 'details', 'images']);

            if (is_numeric($identifier)) {
                $post = $query->where('id', (int) $identifier)->first();
            } else {
                $post = $query->where('slug', $identifier)->first();
            }

            if (!$post) {
                return null;
            }

            return [
                'success' => true,
                'data' => $this->formatJobDetail($post),
            ];
        });

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
            ], 404)->header('Access-Control-Allow-Origin', '*');
        }

        return response()->json($response)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
    }

    /**
     * Get dynamic config & monetization parameters for CSR frontends.
     * GET /api/csr/config
     */
    public function config(Request $request): JsonResponse
    {
        $cacheKey = 'csr_remote_config';

        $data = Cache::remember($cacheKey, 300, function () {
            return [
                'success' => true,
                'site_name' => Setting::get('site_name', 'Portal Karir Indonesia'),
                'contact_wa' => Setting::get('contact_whatsapp', ''),
                'ads' => [
                    'adsterra_socialbar_code' => Setting::get('adsterra_socialbar_code', ''),
                    'adsterra_direct_link' => Setting::get('csr_direct_link', 'https://www.profitablecpmrate.com/example'),
                    'popunder_enabled' => (bool) Setting::get('csr_popunder_enabled', false),
                    'apply_redirect_type' => Setting::get('csr_apply_action', 'direct_link'), // direct_link | original | wa
                ],
                'updated_at' => now()->toIso8601String(),
            ];
        });

        return response()->json($data)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS');
    }

    /**
     * Generate dynamic Google-compliant XML sitemap for any CSR frontend domain.
     * GET /api/csr/sitemap.xml?domain=https://example.vercel.app&page=1&url_format=path
     */
    public function sitemap(Request $request): \Illuminate\Http\Response
    {
        $domain = rtrim((string) $request->input('domain', url('/')), '/');
        // Validate or fallback domain
        if (!filter_var($domain, FILTER_VALIDATE_URL)) {
            $domain = url('/');
        }

        $page = max((int) $request->input('page', 1), 1);
        $perPage = min(max((int) $request->input('limit', 1000), 100), 5000);
        $urlFormat = $request->input('url_format', 'path'); // 'path' (/loker/{slug}) or 'query' (?slug={slug}) or 'hash' (#/loker/{slug})

        $cacheKey = 'csr_sitemap_' . md5("{$domain}_{$page}_{$perPage}_{$urlFormat}");

        $xml = Cache::remember($cacheKey, 1800, function () use ($domain, $page, $perPage, $urlFormat) {
            $catIds = $this->getJobCategoryIds();

            $posts = Post::query()
                ->where('status', 'published')
                ->where(function ($sub) use ($catIds) {
                    if (!empty($catIds)) {
                        $sub->whereIn('category_id', $catIds);
                    } else {
                        $sub->where('title', 'LIKE', '%lowongan%')
                            ->orWhere('title', 'LIKE', '%loker%');
                    }
                })
                ->select(['id', 'slug', 'updated_at', 'published_at'])
                ->latest('published_at')
                ->forPage($page, $perPage)
                ->get();

            $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xmlContent .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // Root homepage
            if ($page === 1) {
                $xmlContent .= "  <url>\n";
                $xmlContent .= "    <loc>" . htmlspecialchars($domain . '/', ENT_XML1) . "</loc>\n";
                $xmlContent .= "    <changefreq>daily</changefreq>\n";
                $xmlContent .= "    <priority>1.0</priority>\n";
                $xmlContent .= "  </url>\n";
            }

            foreach ($posts as $post) {
                if ($urlFormat === 'query') {
                    $jobUrl = $domain . '/?slug=' . urlencode($post->slug);
                } elseif ($urlFormat === 'hash') {
                    $jobUrl = $domain . '/#/loker/' . urlencode($post->slug);
                } else {
                    $jobUrl = $domain . '/loker/' . urlencode($post->slug);
                }

                $lastmod = ($post->updated_at ?: $post->published_at ?: now())->toIso8601String();

                $xmlContent .= "  <url>\n";
                $xmlContent .= "    <loc>" . htmlspecialchars($jobUrl, ENT_XML1) . "</loc>\n";
                $xmlContent .= "    <lastmod>{$lastmod}</lastmod>\n";
                $xmlContent .= "    <changefreq>weekly</changefreq>\n";
                $xmlContent .= "    <priority>0.8</priority>\n";
                $xmlContent .= "  </url>\n";
            }

            $xmlContent .= '</urlset>';

            return $xmlContent;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    /**
     * Format lightweight job summary for listing.
     */
    protected function formatJobSummary(Post $post): array
    {
        $company = $post->getDetailValue(['perusahaan', 'company', 'nama_perusahaan', 'pt', 'employer']) ?? 'Perusahaan Swasta';
        $salary = $post->getDetailValue(['gaji', 'salary', 'rentang_gaji', 'upah']) ?? 'Gaji Kompetitif';
        $location = ($post->city?->name ?? 'Indonesia') . ($post->province?->name ? ', ' . $post->province->name : '');
        $type = $post->getJobEmploymentType();

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'company' => $company,
            'salary' => $salary,
            'location' => $location,
            'type' => $type,
            'featured_image' => $post->verified_image_url ?: $post->primary_image_url,
            'published_at' => $post->published_at ? $post->published_at->diffForHumans() : 'Baru saja',
            'created_at' => $post->created_at?->toIso8601String(),
        ];
    }

    /**
     * Format full job detail.
     */
    protected function formatJobDetail(Post $post): array
    {
        $summary = $this->formatJobSummary($post);

        $requirements = $post->getDetailValue(['persyaratan', 'kualifikasi', 'requirements', 'syarat']) ?? '';
        $responsibilities = $post->getDetailValue(['deskripsi_pekerjaan', 'tanggung_jawab', 'job_description', 'deskripsi']) ?? $post->content;
        $applyLink = $post->getDetailValue(['link_lamar', 'url_sumber', 'source_url', 'apply_url']) ?? '';

        return array_merge($summary, [
            'content' => $post->content,
            'requirements' => $requirements,
            'responsibilities' => $responsibilities,
            'original_apply_url' => $applyLink,
            'address' => $post->address,
            'meta' => [
                'seo_title' => $post->seoTitle(),
                'seo_description' => $post->seoDescription(),
                'salary_schema' => $post->getJobBaseSalary(),
            ],
        ]);
    }
}
