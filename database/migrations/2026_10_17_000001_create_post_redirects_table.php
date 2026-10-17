<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_path', 512)->unique();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_redirects');
    }
};
