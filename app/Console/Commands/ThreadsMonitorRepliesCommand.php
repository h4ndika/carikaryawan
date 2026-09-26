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
    protected $signature = 'threads:monitor-replies 
                            {--posts-limit=100 : Jumlah postingan Threads yang dipindai (default 100 post)}
                            {--reply-hours=48 : Rentang jam ke belakang untuk komentar yang akan dibalas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor komentar masuk di postingan Threads (baru maupun lama) dan balas otomatis menggunakan AI';

    /**
     * Execute the console command.
     */
    public function handle(ThreadsService $service): int
    {
        if (!$service->isConfigured()) {
            $this->warn('Threads credentials belum dikonfigurasi.');
            return self::FAILURE;
        }

        $postsLimit = (int) ($this->option('posts-limit') ?: 100);
        $replyHours = (int) ($this->option('reply-hours') ?: 48);
        $commentSince = Carbon::now()->subHours($replyHours);

        $this->info("Memindai hingga {$postsLimit} postingan Threads untuk komentar dalam {$replyHours} jam terakhir...");

        $threads = $service->getUserThreads(limit: $postsLimit);
        if (empty($threads)) {
            $this->info('Tidak ada postingan yang ditemukan.');
            return self::SUCCESS;
        }

        $repliedCount = 0;

        $ourProfile = $service->verifyProfile();
        $ourUsername = strtolower($ourProfile['username'] ?? 'disekitarcom');

        foreach ($threads as $thread) {
            $mediaId = $thread['id'] ?? null;
            $postText = $thread['text'] ?? '';

            if (!$mediaId) {
                continue;
            }

            // Ambil komentar / balasan pada thread ini (termasuk pada postingan lama)
            $replies = $service->getThreadConversation($mediaId);
            if (empty($replies)) {
                continue;
            }

            foreach ($replies as $reply) {
                $replyId = $reply['id'] ?? null;
                $author = $reply['username'] ?? 'pengguna';
                $commentText = trim($reply['text'] ?? '');
                $replyTime = isset($reply['timestamp']) ? Carbon::parse($reply['timestamp']) : null;

                if (!$replyId || empty($commentText)) {
                    continue;
                }

                // Abaikan komentar yang dibuat lebih lama dari batas waktu jendela (misal > 48 jam lalu)
                if ($replyTime && $replyTime->lt($commentSince)) {
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
