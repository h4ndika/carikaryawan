<?php

namespace App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs;

use App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\Pages\ListGoogleMapsScrapeJobs;
use App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\Tables\GoogleMapsScrapeJobsTable;
use App\Models\GoogleMapsScrapeJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GoogleMapsScrapeJobResource extends Resource
{
    protected static ?string $model = GoogleMapsScrapeJob::class;

    protected static string | UnitEnum | null $navigationGroup = 'Loker';

    protected static ?string $navigationLabel = 'Ekstraksi Loker';

    protected static ?string $modelLabel = 'Ekstraksi Loker';

    protected static ?string $pluralModelLabel = 'Ekstraksi Loker';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlassPlus;

    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return GoogleMapsScrapeJobsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $active = static::getModel()::whereIn('status', ['pending', 'scraping', 'importing'])->count();
        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGoogleMapsScrapeJobs::route('/'),
        ];
    }
}
