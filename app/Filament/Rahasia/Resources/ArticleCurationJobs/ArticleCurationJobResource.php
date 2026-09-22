<?php

namespace App\Filament\Rahasia\Resources\ArticleCurationJobs;

use App\Filament\Rahasia\Resources\ArticleCurationJobs\Pages\ListArticleCurationJobs;
use App\Filament\Rahasia\Resources\ArticleCurationJobs\Tables\ArticleCurationJobsTable;
use App\Models\ArticleCurationJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ArticleCurationJobResource extends Resource
{
    protected static ?string $model = ArticleCurationJob::class;

    protected static string|UnitEnum|null $navigationGroup = 'Konten & Publikasi';

    protected static ?string $navigationLabel = 'Antrean Kurasi AI';

    protected static ?string $modelLabel = 'Antrean Kurasi Artikel';

    protected static ?string $pluralModelLabel = 'Antrean Kurasi Artikel (AI)';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 16;

    public static function table(Table $table): Table
    {
        return ArticleCurationJobsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = static::getModel()::whereIn('status', ['pending', 'processing'])->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleCurationJobs::route('/'),
        ];
    }
}
