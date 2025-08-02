<?php

namespace App\Filament\Rahasia\Resources\Cities\Schemas;

use App\Models\Province;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug'),
                Select::make('province_id')
                    ->label('Province')
                    ->options(Province::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
            ]);
    }
}
