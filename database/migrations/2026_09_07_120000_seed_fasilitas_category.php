<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Category::firstOrCreate(
            ['slug' => 'fasilitas'],
            ['name' => 'Fasilitas']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Category::where('slug', 'fasilitas')->delete();
    }
};
