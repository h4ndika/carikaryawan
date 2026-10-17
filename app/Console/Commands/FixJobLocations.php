<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\JobLocationRepairService;
use Illuminate\Console\Command;

class FixJobLocations extends Command
{
    protected $signature = 'jobs:fix-locations
        {--dry-run : Lihat kandidat tanpa perubahan (default)}
        {--apply : Terapkan hanya ID yang sudah ditinjau}
        {--id=* : ID lowongan yang disetujui}
        {--backup= : Path backup database sebelum apply}';

    protected $description = 'Koreksi kota eksplisit yang tidak ambigu dan simpan redirect URL lama';

    public function handle(JobLocationRepairService $repair): int
    {
        $apply = $this->option('apply');
        $ids = $this->option('id');
        $backup = $this->option('backup');
        if ($apply && ($this->option('dry-run') || !$ids || !$backup || !is_file($backup) || filesize($backup) === 0)) {
            $this->error('Apply perlu --id yang telah ditinjau dan --backup file database yang tidak kosong, tanpa --dry-run.');

            return self::FAILURE;
        }
        foreach ($ids as $id) {
            if (!ctype_digit((string) $id) || (int) $id < 1) {
                $this->error('ID harus bilangan positif.');

                return self::FAILURE;
            }
        }

        $rows = [];
        $posts = Post::published()->whereHas('category', fn ($query) => $query->jobs())
            ->when($ids, fn ($query) => $query->whereIn('id', $ids))
            ->with(['category', 'city'])->get();
        foreach ($posts as $post) {
            $city = $repair->candidate($post);
            if (!$city) {
                $this->line("Lewati #{$post->id}: kota sama, tidak jelas, atau ambigu.");
                continue;
            }
            $oldUrl = $post->url();
            $preview = clone $post;
            $preview->setRelation('city', $city);
            $rows[] = [$post->id, $post->city?->name ?? '-', $city->name, 'Satu kota eksplisit dalam judul/konten', $oldUrl, $preview->url()];
            if ($apply) {
                $repair->apply($post, $city);
            }
        }
        $this->table(['ID', 'Kota lama', 'Kota baru', 'Alasan', 'URL lama', 'URL baru'], $rows);
        $this->info(($apply ? 'Diterapkan: ' : 'Dry-run, tanpa perubahan: ') . count($rows) . ' kandidat.');

        return self::SUCCESS;
    }
}
