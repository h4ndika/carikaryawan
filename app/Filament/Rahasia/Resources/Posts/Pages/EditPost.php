<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use App\Models\PostDetail;
use App\Models\PostImage;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    /** @var array */
    protected array $detailsData = [];

    /** @var array */
    protected array $imagesData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['details'] = PostDetail::where('post_id', $this->record->id)
            ->get(['meta_key', 'meta_value'])
            ->toArray();

        $data['images'] = PostImage::where('post_id', $this->record->id)
            ->orderBy('order')
            ->get(['image_url', 'caption', 'order'])
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->detailsData = $data['details'] ?? [];
        unset($data['details']);

        $this->imagesData = $data['images'] ?? [];
        unset($data['images']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Details: always replace (empty = clear all)
        DB::table('post_details')->where('post_id', $this->record->id)->delete();
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

        // Images: always replace (empty = clear all)
        DB::table('post_images')->where('post_id', $this->record->id)->delete();
        if (!empty($this->imagesData)) {
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
}