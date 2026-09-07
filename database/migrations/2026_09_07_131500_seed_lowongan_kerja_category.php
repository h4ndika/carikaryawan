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
            ['slug' => 'lowongan-kerja'],
            ['name' => 'Lowongan Kerja']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Category::where('slug', 'lowongan-kerja')->delete();
    }
};
