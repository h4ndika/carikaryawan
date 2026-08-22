<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Pages;

use App\Filament\Rahasia\Resources\ApifyAccounts\ApifyAccountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditApifyAccount extends EditRecord
{
    protected static string $resource = ApifyAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
