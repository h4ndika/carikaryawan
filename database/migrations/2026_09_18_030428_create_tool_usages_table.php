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
        Schema::create('tool_usages', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name', 50)->index(); // 'cover_letter', 'cv_builder', 'interview_prep', etc.
            $table->string('action', 50)->default('generate')->index(); // 'generate', 'polish_summary', 'polish_achievement', 'export_pdf', etc.
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('target_position', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // extra payload like level, focus, language, time_taken_ms
            $table->string('status', 20)->default('success'); // 'success', 'failed'
            $table->timestamps();

            $table->index(['tool_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_usages');
    }
};
