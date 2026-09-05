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
        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('prompt');
            $table->string('provider', 50)->default('openai-compatible');
            $table->string('model', 100)->nullable();
            $table->json('report_data')->nullable();
            $table->longText('raw_text')->nullable();
            $table->json('tool_calls')->nullable();
            $table->decimal('duration', 8, 2)->nullable();
            $table->json('tokens')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_reports');
    }
};
