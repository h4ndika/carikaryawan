<?php

namespace App\Filament\Rahasia\Resources\Provinces\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class ProvinceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('slug')
            ]);
    }
}
