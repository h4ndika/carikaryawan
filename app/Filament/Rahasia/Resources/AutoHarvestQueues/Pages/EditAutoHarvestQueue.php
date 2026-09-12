<?php

namespace App\Filament\Rahasia\Resources\AutoHarvestQueues\Pages;

use App\Filament\Rahasia\Resources\AutoHarvestQueues\AutoHarvestQueueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAutoHarvestQueue extends EditRecord
{
    protected static string $resource = AutoHarvestQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
