<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Pages;

use App\Filament\Rahasia\Resources\ApifyAccounts\ApifyAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApifyAccounts extends ListRecords
{
    protected static string $resource = ApifyAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Akun Apify'),
        ];
    }
}
