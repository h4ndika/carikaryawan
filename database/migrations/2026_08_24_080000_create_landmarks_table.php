<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('landmarks')) {
            Schema::create('landmarks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('province_id')
                    ->nullable()
                    ->constrained('provinces')
                    ->nullOnDelete();
                $table->foreignId('city_id')
                    ->constrained('cities')
                    ->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('type');
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->decimal('default_radius_km', 8, 2)->default(3);
                $table->text('description')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();

                $table->index(['city_id', 'type']);
                $table->index(['latitude', 'longitude']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('landmarks');
    }
};
