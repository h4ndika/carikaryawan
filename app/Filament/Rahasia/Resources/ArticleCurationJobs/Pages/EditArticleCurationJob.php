<?php

namespace App\Filament\Rahasia\Resources\ArticleCurationJobs\Pages;

use App\Filament\Rahasia\Resources\ArticleCurationJobs\ArticleCurationJobResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditArticleCurationJob extends EditRecord
{
    protected static string $resource = ArticleCurationJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
