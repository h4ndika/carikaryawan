<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class DisekitarDatabaseTool implements Tool
{
    public function description(): string
    {
        return <<<'DESC'
Execute a safe, read-only SQL query (SELECT, DESCRIBE, SHOW) on the DiSekitar database to retrieve live facts, counts, statistics, schema, and records.
The available tables and exact columns are:
- posts: id, category_id, city_id, province_id, title, slug, status ('draft','published'), rating, reviews_count, views_count, address, latitude, longitude, published_at, created_at
- categories: id, name, slug
- cities: id, name, slug, province_id
- provinces: id, name, slug
- landmarks: id, city_id, province_id, name, slug, type, latitude, longitude, default_radius_km, description
- page_views: id, post_id, path, url, ip_hash, referrer, referrer_domain, device, viewed_date, created_at
- post_details: id, post_id, field_name, field_value
- ai_queues: id, post_id, status ('pending','processing','completed','failed'), error, created_at
- photo_scrape_jobs: id, post_id, status ('pending','processing','completed','failed'), photos_count, created_at

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
