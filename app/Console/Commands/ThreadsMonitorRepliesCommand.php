<?php

namespace App\Console\Commands;

use App\Models\ThreadsEngagement;
use App\Services\ThreadsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ThreadsMonitorRepliesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'threads:monitor-replies {--hours=24 : Jam rentang postingan yang dimonitor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor komentar masuk di postingan Threads kita dan balas otomatis menggunakan AI';

    /**
     * Execute the console command.
     */
    public function handle(ThreadsService $service): int
    {
        if (!$service->isConfigured()) {
            $this->warn('Threads credentials belum dikonfigurasi.');
            return self::FAILURE;
        }

        $hours = (int) $this->option('hours');
        $since = Carbon::now()->subHours($hours);

        $this->info("Memeriksa postingan Threads dalam {$hours} jam terakhir...");

        $threads = $service->getUserThreads(limit: 25);
        if (empty($threads)) {
            $this->info('Tidak ada postingan yang ditemukan.');
            return self::SUCCESS;
        }

        $repliedCount = 0;

        foreach ($threads as $thread) {
            $mediaId = $thread['id'] ?? null;
            $postText = $thread['text'] ?? '';
            $postTime = isset($thread['timestamp']) ? Carbon::parse($thread['timestamp']) : null;

            if (!$mediaId || ($postTime && $postTime->lt($since))) {
                continue;
            }

            // Ambil komentar / balasan pada thread ini
            $replies = $service->getThreadConversation($mediaId);
            if (empty($replies)) {
                continue;
            }

            $ourProfile = $service->verifyProfile();
            $ourUsername = strtolower($ourProfile['username'] ?? 'disekitarcom');

            foreach ($replies as $reply) {
                $replyId = $reply['id'] ?? null;
                $author = $reply['username'] ?? 'pengguna';
                $commentText = trim($reply['text'] ?? '');

                if (!$replyId || empty($commentText)) {
                    continue;
                }

                // Jangan reply komentar akun kita sendiri
                if (strtolower($author) === $ourUsername) {
                    continue;
                }

                // Cek apakah sudah pernah diproses di database
                $exists = ThreadsEngagement::where('reply_id', $replyId)->exists();
                if ($exists) {
                    continue;
                }

                $this->line("Ditemukan komentar baru dari @{$author} pada thread {$mediaId}: \"{$commentText}\"");

                // Generate balasan AI
                $aiReply = $service->generateAiReply($postText, $author, $commentText);

                if (empty($aiReply)) {
                    $this->warn("Gagal generate AI reply untuk reply ID {$replyId}");
                    ThreadsEngagement::create([
                        'parent_media_id' => $mediaId,
                        'reply_id' => $replyId,
                        'author_username' => $author,
                        'author_text' => $commentText,
                        'status' => 'skipped',
                    ]);
                    continue;
                }

                try {
                    $result = $service->replyToThread($replyId, $aiReply);
                    $ourReplyId = $result['id'] ?? null;

                    ThreadsEngagement::create([
                        'parent_media_id' => $mediaId,
                        'reply_id' => $replyId,
                        'author_username' => $author,
                        'author_text' => $commentText,
                        'reply_text' => $aiReply,
                        'our_reply_id' => $ourReplyId,
                        'status' => 'replied',
                        'replied_at' => now(),
                    ]);

                    $this->info("Berhasil membalas @{$author}: \"{$aiReply}\"");
                    $repliedCount++;

                    // Beri jeda 3 detik antar reply agar tidak memicu rate limit
                    sleep(3);
                } catch (\Throwable $e) {
                    Log::error("Threads reply error [{$replyId}]: " . $e->getMessage());
                    $this->error("Error reply ke @{$author}: " . $e->getMessage());

                    ThreadsEngagement::create([
                        'parent_media_id' => $mediaId,
                        'reply_id' => $replyId,
                        'author_username' => $author,
                        'author_text' => $commentText,
                        'reply_text' => $aiReply,
                        'status' => 'failed',
                    ]);
                }
            }
        }

        $this->info("Selesai. Total komentar yang dibalas: {$repliedCount}");
        return self::SUCCESS;
    }
}
