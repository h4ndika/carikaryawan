<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class CariKaryawanDatabaseTool implements Tool
{
    public function description(): string
    {
        return <<<'DESC'
Execute a safe, read-only SQL query (SELECT, DESCRIBE, SHOW) on the CariKaryawan job-board database to retrieve live facts, counts, statistics, schema, and records.
The available tables and exact columns are:
- posts: job listings. id, category_id, city_id, province_id, title, slug, excerpt, content, status ('draft','published'), place_id (canonical job_ dedup key), phone, website (original apply URL), views_count, published_at, expired_at (job closes; NULL means 21 days after published_at), posted_to_threads_at, created_at, deleted_at (soft delete: add `deleted_at IS NULL`)
- categories: id, name, slug (job categories contain 'lowongan' or 'loker')
- cities: id, name, slug, province_id
- provinces: id, name, slug
- page_views: id, post_id, path, url, ip_hash, referrer, referrer_domain, device, viewed_date, created_at
- post_details: id, post_id, meta_key, meta_value (e.g. posisi, perusahaan, lokasi, gaji, tipe_pekerjaan, cara_melamar)
- post_images: id, post_id, image_url, caption, order
- ai_generation_jobs: id, post_id, post_title, status ('pending','processing','completed','failed'), error_message, started_at, finished_at, created_at
- google_maps_scrape_jobs: id, engine ('jobstreet','glints'), query, location, city_id, category_id, status, total_scraped, total_imported, total_updated, total_skipped, total_failed, error_message, created_at (job extraction runs)

Important Schema Notes:
- page_views: to query views by date, use `viewed_date = CURDATE()` (e.g. `SELECT COUNT(*) FROM page_views WHERE viewed_date = CURDATE()`) or `DATE(created_at) = CURDATE()`. The column `visited_at` DOES NOT EXIST, use `viewed_date` or `created_at`.
- You can also run `DESCRIBE table_name` or `SHOW COLUMNS FROM table_name` anytime you need to inspect column names.

Only read queries (SELECT, DESCRIBE, SHOW, EXPLAIN) are permitted. Never use INSERT, UPDATE, DELETE, ALTER, DROP, TRUNCATE, or CREATE.
Always use appropriate JOINs when querying by city or category name (e.g. JOIN cities ON posts.city_id = cities.id).
DESC;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
                ->string()
                ->description('The SQL SELECT, DESCRIBE, or SHOW query to execute (e.g. SELECT COUNT(*) as total FROM page_views WHERE viewed_date = CURDATE())')
                ->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $query = trim($request->string('query'));

        // Strict read-only validation
        if (! preg_match('/^\s*(SELECT|DESCRIBE|DESC|SHOW|EXPLAIN)\b/i', $query)) {
            return (string) json_encode([
                'error' => 'Hanya query baca (SELECT, DESCRIBE, SHOW) yang diizinkan untuk keamanan data.',
            ]);
        }

        // Disallow dangerous keywords
        if (preg_match('/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|REPLACE|GRANT|REVOKE|EXEC|SET|FLUSH)\b/i', $query)) {
            return (string) json_encode([
                'error' => 'Query tidak diizinkan: mengandung kata kunci modifikasi database.',
            ]);
        }

        // Append safe LIMIT 50 if SELECT query without LIMIT
        if (preg_match('/^\s*SELECT\b/i', $query) && ! preg_match('/\bLIMIT\s+\d+/i', $query)) {
            $query = rtrim($query, ';') . ' LIMIT 50';
        }

        try {
            $results = DB::select($query);
            return (string) json_encode([
                'query_executed' => $query,
                'total_rows' => count($results),
                'data' => $results,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            return (string) json_encode([
                'error' => 'Gagal mengeksekusi query: ' . $e->getMessage(),
                'query_attempted' => $query,
            ]);
        }
    }
}
