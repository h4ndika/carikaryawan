<?php

namespace App\Filament\Rahasia\Resources\Posts\Schemas;

use App\Models\City;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
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
                Select::make('city_id')
                    ->label('City')
                    ->options(City::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $city = City::find($state);
                            if ($city && $city->province_id) {
                                $set('province_id', $city->province_id);
                            }
                        }
                    }),
                Select::make('province_id')
                    ->label('Province')
                    ->options(\App\Models\Province::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Textarea::make('contenct')
                    ->label('Content'),
                Textarea::make('excerpt')
                    ->label('Excerpt'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required(),

            ]);
    }
}
