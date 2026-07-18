<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    /** @var array */
    protected array $detailsData = [];

    /** @var array */
    protected array $imagesData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->detailsData = $data['details'] ?? [];
        unset($data['details']);

        $this->imagesData = $data['images'] ?? [];
        unset($data['images']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->detailsData)) {
            $details = [];
            foreach ($this->detailsData as $detail) {
                $details[] = [
                    'post_id' => $this->record->id,
                    'meta_key' => $detail['meta_key'],
                    'meta_value' => $detail['meta_value'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('post_details')->insert($details);
        }

        $this->syncImages();
    }

    protected function syncImages(): void
    {
        if (empty($this->imagesData)) {
            return;
        }

        $rows = [];
        foreach (array_values($this->imagesData) as $index => $image) {
            $url = $image['image_url'] ?? null;
            if (blank($url)) {
                continue;
            }
            $rows[] = [
                'post_id' => $this->record->id,
                'image_url' => $url,
                'caption' => $image['caption'] ?? null,
                'order' => $image['order'] ?? $index,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($rows)) {
            DB::table('post_images')->insert($rows);
        }
    }
}