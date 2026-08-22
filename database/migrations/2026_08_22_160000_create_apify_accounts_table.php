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
        Schema::create('apify_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable()->comment('Label/Nama Akun');
            $table->string('email')->nullable()->comment('Email akun Apify');
            $table->text('token')->comment('API Token Apify');
            $table->enum('status', ['active', 'exhausted', 'invalid', 'disabled'])->default('active');
            $table->unsignedInteger('usage_count')->default(0)->comment('Berapa kali token ini sudah digunakan untuk scrape');
            $table->timestamp('last_used_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'usage_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apify_accounts');
    }
};
