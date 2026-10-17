<?php

namespace App\Services;

use App\Helpers\AddressParser;
use App\Models\City;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class JobLocationRepairService
{
    public function candidate(Post $post): ?City
    {
        // ponytail: explicit city mentions only; ambiguous/generated copy needs manual source review.
        $text = html_entity_decode(strip_tags($post->title . ' ' . $post->content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ids = AddressParser::matchingCityIds($text);

        return count($ids) === 1 && $ids[0] !== $post->city_id ? City::find($ids[0]) : null;
    }

    public function apply(Post $post, City $city): void
    {
        DB::transaction(function () use ($post, $city) {
            $post = Post::with(['category', 'city'])->lockForUpdate()->findOrFail($post->id);
            if ($post->status !== 'published' || !$post->isJob() || $this->candidate($post)?->id !== $city->id) {
                throw new RuntimeException('Data berubah; ulangi dry-run sebelum koreksi.');
            }

            $oldUrl = $post->url();
            $oldPath = parse_url($oldUrl, PHP_URL_PATH);
            $existing = DB::table('post_redirects')->where('old_path', $oldPath)->first();
            if ($existing && $existing->post_id !== $post->id) {
                throw new RuntimeException('URL lama sudah dipakai redirect lowongan lain.');
            }
            if (!$existing) {
                DB::table('post_redirects')->insert(['old_path' => $oldPath, 'post_id' => $post->id]);
            }

            $post->city_id = $city->id;
            $post->province_id = $city->province_id;
            if ($post->canonical_url === $oldUrl) {
                $post->canonical_url = null;
            }
            $post->save();
            $post->details()->updateOrCreate(['meta_key' => 'lokasi'], ['meta_value' => $city->name]);
        });
    }
}
