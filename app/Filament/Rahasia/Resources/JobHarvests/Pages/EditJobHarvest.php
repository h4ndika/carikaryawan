<?php

namespace App\Filament\Rahasia\Resources\JobHarvests\Pages;

use App\Filament\Rahasia\Resources\JobHarvests\JobHarvestResource;
use App\Models\PostDetail;
use App\Models\PostImage;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditJobHarvest extends EditRecord
{
    protected static string $resource = JobHarvestResource::class;

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
        $data['details'] = $this->record->details()
            ->get(['key', 'value'])
            ->toArray();

        $data['images'] = $this->record->images()
            ->orderBy('id')
            ->pluck('image_url')
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->detailsData = $data['details'] ?? [];
        $this->imagesData = $data['images'] ?? [];

        unset($data['details'], $data['images']);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        DB::transaction(function () use ($record) {
            $record->details()->delete();
            if (!empty($this->detailsData)) {
                $detailsToInsert = collect($this->detailsData)
                    ->filter(fn ($item) => !empty($item['key']) && !empty($item['value']))
                    ->map(fn ($item) => [
                        'post_id' => $record->id,
                        'key' => $item['key'],
                        'value' => $item['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all();

                if (!empty($detailsToInsert)) {
                    PostDetail::insert($detailsToInsert);
                }
            }

            $record->images()->delete();
            if (!empty($this->imagesData)) {
                $imagesToInsert = collect($this->imagesData)
                    ->filter(fn ($url) => !empty($url))
                    ->map(fn ($url) => [
                        'post_id' => $record->id,
                        'image_url' => $url,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->all();

                if (!empty($imagesToInsert)) {
                    PostImage::insert($imagesToInsert);
                }
            }
        });
    }
}
