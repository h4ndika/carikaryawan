<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;
}
