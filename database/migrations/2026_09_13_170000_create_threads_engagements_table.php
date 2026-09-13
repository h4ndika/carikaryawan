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
        Schema::create('threads_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('parent_media_id')->index(); // ID postingan Threads kita
            $table->string('reply_id')->unique(); // ID komentar dari pengguna di Threads
            $table->string('author_username')->nullable();
            $table->text('author_text'); // Teks komentar dari audiens
            $table->text('reply_text')->nullable(); // Teks balasan kita
            $table->string('our_reply_id')->nullable(); // ID media balasan kita di Threads
            $table->string('status')->default('replied'); // replied, skipped, failed
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads_engagements');
    }
};
