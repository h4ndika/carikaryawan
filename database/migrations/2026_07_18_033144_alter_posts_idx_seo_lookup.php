<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $blueprint) {
            // Menambahkan composite index untuk optimasi kueri SEO & Routing Subdomain
            $blueprint->index(
                ['status', 'category_id', 'city_id', 'published_at'],
                'idx_seo_lookup'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $blueprint) {
            // Menghapus index jika migration di-rollback
            $blueprint->dropIndex('idx_seo_lookup');
        });
    }
};
