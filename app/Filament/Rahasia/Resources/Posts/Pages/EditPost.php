<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

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
        // Ambil detail dari tabel post_details
        $details = \App\Models\PostDetail::where('post_id', $this->record->id)
            ->get(['meta_key', 'meta_value'])
            ->toArray();

        // Set ke repeater
        $data['details'] = $details;

        return $data;
    }

    /**
     * Holds the details data temporarily before saving to post_details table.
     *
     * @var array
     */
    protected array $detailsData = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan repeater ke variabel agar bisa diolah setelah posts tersimpan
        $this->detailsData = $data['details'] ?? [];
        unset($data['details']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (!empty($this->detailsData)) {
            // Delete existing details for this post
            DB::table('post_details')->where('post_id', $this->record->id)->delete();

            // Insert new details
            $details = [];
            foreach ($this->detailsData as $detail) {
                $details[] = [
                    'post_id'    => $this->record->id,
                    'meta_key'   => $detail['meta_key'],
                    'meta_value' => $detail['meta_value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($details)) {
                DB::table('post_details')->insert($details);
            }
        }
    }
}
