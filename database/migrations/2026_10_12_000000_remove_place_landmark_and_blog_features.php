<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drain legacy jobs before deploying code that no longer contains their handlers.
        if (DB::table('jobs')->where(function ($query) {
            foreach (['CurateArticleJob', 'ProcessBatchPhotoScrapeJob', 'RunGoogleMapsScrapeJob'] as $job) {
                $query->orWhere('payload', 'like', "%{$job}%");
            }
        })->exists()) {
            throw new RuntimeException('Drain extraction, photo and article queues before running this irreversible migration.');
        }

        foreach (['article_curation_jobs', 'articles', 'auto_harvest_queues', 'photo_scrape_jobs', 'landmarks'] as $table) {
            Schema::dropIfExists($table);
        }

        $jobCategoryIds = DB::table('categories')->where(function ($query) {
            $query->whereRaw("LOWER(slug) LIKE '%lowongan%'")
                ->orWhereRaw("LOWER(slug) LIKE '%loker%'")
                ->orWhereRaw("LOWER(name) LIKE '%lowongan%'")
                ->orWhereRaw("LOWER(name) LIKE '%loker%'");
        })->pluck('id');

        // Query builder includes soft-deleted posts; child FKs cascade or nullOnDelete.
        DB::table('posts')->whereNotIn('category_id', $jobCategoryIds)->delete();
        DB::table('google_maps_scrape_jobs')->whereNotIn('engine', ['jobstreet', 'glints'])->delete();
        DB::table('categories')->whereNotIn('id', $jobCategoryIds)->delete();

        $columns = array_filter(
            ['latitude', 'longitude', 'rating', 'reviews_count', 'google_maps_url', 'price_level', 'cid'],
            fn ($column) => Schema::hasColumn('posts', $column),
        );
        if ($columns) {
            Schema::table('posts', fn (Blueprint $table) => $table->dropColumn(array_values($columns)));
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Removed content cannot be restored by rollback. Restore the pre-migration database backup.');
    }
};
