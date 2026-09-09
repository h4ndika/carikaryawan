<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('expired_at')->nullable()->after('published_at')->index();
        });

        try {
            $jobCategoryIds = \Illuminate\Support\Facades\DB::table('categories')
                ->where('slug', 'like', '%lowongan%')
                ->pluck('id');

            if ($jobCategoryIds->isNotEmpty()) {
                \Illuminate\Support\Facades\DB::table('posts')
                    ->whereIn('category_id', $jobCategoryIds)
                    ->whereNull('expired_at')
                    ->update([
                        'expired_at' => \Illuminate\Support\Facades\DB::raw('DATE_ADD(COALESCE(published_at, created_at), INTERVAL 21 DAY)')
                    ]);
            }
        } catch (\Throwable) {
            // Ignore if in environments that differ
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['expired_at']);
            $table->dropColumn('expired_at');
        });
    }
};
