<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    /**
     * Holds the details data temporarily before saving to post_details table.
     *
     * @var array
     */
    protected array $detailsData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan repeater ke variabel agar bisa diolah setelah posts tersimpan
        $this->detailsData = $data['details'] ?? [];
        unset($data['details']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (!empty($this->detailsData)) {
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
            DB::table('post_details')->insert($details);
        }
    }
}
