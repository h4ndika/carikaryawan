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
            // Google Maps unique identifier for duplicate detection
            $table->string('place_id')->nullable()->unique()->after('slug');
            
            // GPS coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('place_id');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Contact information
            $table->string('phone')->nullable()->after('longitude');
            $table->string('website')->nullable()->after('phone');
            
            // Rating and reviews
            $table->decimal('rating', 3, 2)->nullable()->after('website');
            $table->unsignedInteger('reviews_count')->nullable()->default(0)->after('rating');
            
            // Google Maps metadata
            $table->string('google_maps_url')->nullable()->after('reviews_count');
            $table->string('price_level')->nullable()->after('google_maps_url');
            $table->string('cid')->nullable()->after('price_level');
            
            // Add index for performance
            $table->index('place_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['place_id']);
            $table->dropColumn([
                'place_id',
                'latitude',
                'longitude',
                'phone',
                'website',
                'rating',
                'reviews_count',
                'google_maps_url',
                'price_level',
                'cid',
            ]);
        });
    }
};
