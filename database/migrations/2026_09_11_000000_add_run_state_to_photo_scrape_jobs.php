<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_scrape_jobs', function (Blueprint $table) {
            $table->uuid('processing_token')->nullable()->index();
            $table->unsignedSmallInteger('poll_failures')->default(0);
            $table->string('phase')->nullable();
            $table->string('apify_run_id')->nullable();
            $table->string('apify_dataset_id')->nullable();
            $table->text('apify_token')->nullable();
            $table->timestamp('deadline_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('photo_scrape_jobs', function (Blueprint $table) {
            $table->dropIndex(['processing_token']);
            $table->dropIndex(['deadline_at']);
            $table->dropColumn(['processing_token', 'poll_failures', 'phase', 'apify_run_id', 'apify_dataset_id', 'apify_token', 'deadline_at']);
        });
    }
};
