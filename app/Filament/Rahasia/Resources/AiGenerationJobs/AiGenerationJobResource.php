<?php

namespace App\Filament\Rahasia\Resources\AiGenerationJobs;

use App\Filament\Rahasia\Resources\AiGenerationJobs\Pages\ListAiGenerationJobs;
use App\Filament\Rahasia\Resources\AiGenerationJobs\Tables\AiGenerationJobsTable;
use App\Models\AiGenerationJob;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AiGenerationJobResource extends Resource
{
    protected static ?string $model = AiGenerationJob::class;

    protected static string | UnitEnum | null $navigationGroup = 'Post';

    protected static ?string $navigationLabel = 'Antrean AI';

    protected static ?string $modelLabel = 'Antrean AI';

    protected static ?string $pluralModelLabel = 'Antrean Generate AI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return AiGenerationJobsTable::configure($table);
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
            'index' => ListAiGenerationJobs::route('/'),
        ];
    }
}
