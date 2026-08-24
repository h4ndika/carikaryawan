<?php

namespace App\Filament\Rahasia\Resources\Landmarks\Schemas;

use App\Models\City;
use App\Models\Province;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LandmarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('type')
                    ->label('Type / Category')
                    ->placeholder('e.g. transit_hub, tourist_attraction, station, mall, airport')
                    ->required(),
                Select::make('province_id')
                    ->label('Province')
                    ->options(Province::query()->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
                Select::make('city_id')
                    ->label('City')
                    ->options(function (callable $get) {
                        $provinceId = $get('province_id');
                        if ($provinceId) {
                            return City::where('province_id', $provinceId)->pluck('name', 'id');
                        }
                        return City::query()->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('latitude')
                    ->numeric()
                    ->required(),
                TextInput::make('longitude')
                    ->numeric()
                    ->required(),
                TextInput::make('default_radius_km')
                    ->label('Default Radius (km)')
                    ->numeric()
                    ->default(3)
                    ->required(),
                Textarea::make('description')
                    ->rows(3),
                TextInput::make('meta_title')
                    ->placeholder('Custom SEO title for this landmark page'),
                Textarea::make('meta_description')
                    ->placeholder('Custom SEO description')
                    ->rows(2),
            ]);
    }
}
