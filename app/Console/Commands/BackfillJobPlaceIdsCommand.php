<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class BackfillJobPlaceIdsCommand extends Command
{
    protected $signature = 'carikaryawan:backfill-job-place-ids';
    protected $description = 'Backfill canonical place_id for existing job postings in database';

    public function handle(): int
    {
        $this->info('Finding job posts without place_id...');

        $posts = Post::query()
            ->with(['details', 'city', 'category'])
            ->whereNull('place_id')
            ->get()
            ->filter(fn (Post $post) => $post->isJob());

        $this->info("Found {$posts->count()} job posts to process.");

        $updated = 0;
        $duplicates = 0;

        foreach ($posts as $post) {
            $details = $post->details->pluck('meta_value', 'meta_key')->toArray();

            $pos = $details['posisi'] ?? null;
            $comp = $details['perusahaan'] ?? null;
            $loc = $details['lokasi'] ?? ($post->city?->name ?? '');

            if (!$pos || !$comp) {
                if (str_contains($post->title, ' - ')) {
                    $parts = explode(' - ', $post->title, 2);
                    $pos = $pos ?: trim($parts[0]);
                    $comp = $comp ?: trim($parts[1]);
                } else {
                    $pos = $pos ?: $post->title;
                    $comp = $comp ?: 'perusahaan';
                }
            }

            $placeId = \App\Models\Post::jobPlaceId($comp, $pos, $loc);

            $existingWithPlaceId = Post::where('place_id', $placeId)->where('id', '!=', $post->id)->first();
            if ($existingWithPlaceId) {
                $this->warn("Duplicate detected for ID {$post->id} ('{$post->title}'). Matches post ID {$existingWithPlaceId->id}.");
                $duplicates++;
                continue;
            }

            $post->update(['place_id' => $placeId]);
            $updated++;
        }

        $this->info("Completed. Updated: {$updated}, Duplicates detected: {$duplicates}");
        return Command::SUCCESS;
    }
}
