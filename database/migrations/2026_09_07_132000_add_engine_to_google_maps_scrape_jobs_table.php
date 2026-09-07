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
        Schema::table('google_maps_scrape_jobs', function (Blueprint $table) {
            $table->string('engine')->default('google-maps')->after('id')->comment('google-maps, google-jobs, instagram');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('google_maps_scrape_jobs', function (Blueprint $table) {
            $table->dropColumn('engine');
        });
    }
};
