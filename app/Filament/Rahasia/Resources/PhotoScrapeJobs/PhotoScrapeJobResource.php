<?php

namespace App\Filament\Rahasia\Resources\PhotoScrapeJobs;

use App\Filament\Rahasia\Resources\PhotoScrapeJobs\Pages\ListPhotoScrapeJobs;
use App\Filament\Rahasia\Resources\PhotoScrapeJobs\Tables\PhotoScrapeJobsTable;
use App\Models\PhotoScrapeJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PhotoScrapeJobResource extends Resource
{
    protected static ?string $model = PhotoScrapeJob::class;

    protected static string | UnitEnum | null $navigationGroup = 'Post';

    protected static ?string $navigationLabel = 'Antrean Scrape Foto';

    protected static ?string $modelLabel = 'Antrean Foto';

    protected static ?string $pluralModelLabel = 'Antrean Scrape Foto';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return PhotoScrapeJobsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::whereIn('status', ['pending', 'processing'])->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPhotoScrapeJobs::route('/'),
        ];
    }
}
