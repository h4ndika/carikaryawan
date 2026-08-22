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
        Schema::create('google_maps_scrape_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('query')->comment('Kata kunci pencarian Google Maps');
            $table->string('location')->nullable()->comment('Lokasi/Kota spesifik');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->unsignedInteger('target_limit')->default(20)->comment('Jumlah target tempat');
            $table->boolean('include_images')->default(true);
            $table->boolean('include_reviews')->default(false);
            $table->boolean('publish_imported')->default(false)->comment('Otomatis ubah status post menjadi published');
            $table->foreignId('apify_account_id')->nullable()->constrained('apify_accounts')->nullOnDelete();
            $table->string('apify_run_id')->nullable();
            $table->string('apify_dataset_id')->nullable();
            $table->enum('status', ['pending', 'scraping', 'importing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('total_scraped')->default(0);
            $table->unsignedInteger('total_imported')->default(0);
            $table->unsignedInteger('total_updated')->default(0);
            $table->unsignedInteger('total_skipped')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_maps_scrape_jobs');
    }
};
