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
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('path', 255)->index();
            $table->string('url', 500)->nullable();
            $table->string('ip_hash', 64)->index();
            $table->string('referrer', 500)->nullable();
            $table->string('referrer_domain', 150)->nullable()->index();
            $table->string('device', 20)->default('desktop')->index();
            $table->date('viewed_date')->index();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['viewed_date', 'ip_hash']);
            $table->index(['viewed_date', 'post_id']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedBigInteger('views_count')->default(0)->after('status')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_views');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['views_count']);
            $table->dropColumn('views_count');
        });
    }
};
