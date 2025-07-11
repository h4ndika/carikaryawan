<?php

namespace App\Filament\Rahasia\Resources\Provinces\Pages;

use App\Filament\Rahasia\Resources\Provinces\ProvinceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProvince extends EditRecord
{
    protected static string $resource = ProvinceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
