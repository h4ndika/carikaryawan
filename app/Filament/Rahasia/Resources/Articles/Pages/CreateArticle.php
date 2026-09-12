<?php

namespace App\Filament\Rahasia\Resources\Articles\Pages;

use App\Filament\Rahasia\Resources\Articles\ArticleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArticle extends CreateRecord
{
    protected static string $resource = ArticleResource::class;
}
