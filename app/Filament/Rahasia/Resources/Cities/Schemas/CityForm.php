<?php

namespace App\Filament\Rahasia\Resources\Cities\Schemas;

use App\Models\Province;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('slug'),
                Select::make('province_id')
                    ->label('Province')
                    ->options(Province::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }
}
