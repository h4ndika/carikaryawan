<?php

namespace App\Filament\Rahasia\Resources\Landmarks;

use App\Filament\Rahasia\Resources\Landmarks\Pages\CreateLandmark;
use App\Filament\Rahasia\Resources\Landmarks\Pages\EditLandmark;
use App\Filament\Rahasia\Resources\Landmarks\Pages\ListLandmarks;
use App\Filament\Rahasia\Resources\Landmarks\Schemas\LandmarkForm;
use App\Filament\Rahasia\Resources\Landmarks\Tables\LandmarksTable;
use App\Models\Landmark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LandmarkResource extends Resource
{
    protected static ?string $model = Landmark::class;

    protected static string | UnitEnum | null $navigationGroup = 'Area';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    public static function form(Schema $schema): Schema
    {
        return LandmarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LandmarksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLandmarks::route('/'),
            'create' => CreateLandmark::route('/create'),
            'edit' => EditLandmark::route('/{record}/edit'),
        ];
    }
}
