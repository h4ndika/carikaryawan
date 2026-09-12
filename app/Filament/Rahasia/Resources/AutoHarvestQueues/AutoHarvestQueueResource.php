<?php

namespace App\Filament\Rahasia\Resources\AutoHarvestQueues;

use App\Filament\Rahasia\Resources\AutoHarvestQueues\Pages\CreateAutoHarvestQueue;
use App\Filament\Rahasia\Resources\AutoHarvestQueues\Pages\EditAutoHarvestQueue;
use App\Filament\Rahasia\Resources\AutoHarvestQueues\Pages\ListAutoHarvestQueues;
use App\Filament\Rahasia\Resources\AutoHarvestQueues\Schemas\AutoHarvestQueueForm;
use App\Filament\Rahasia\Resources\AutoHarvestQueues\Tables\AutoHarvestQueuesTable;
use App\Models\AutoHarvestQueue;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AutoHarvestQueueResource extends Resource
{
    protected static ?string $model = AutoHarvestQueue::class;

    protected static ?string $navigationLabel = 'Stok Auto-Harvest';
    protected static ?string $modelLabel = 'Stok Auto-Harvest';
    protected static ?string $pluralModelLabel = 'Stok Auto-Harvest';
    protected static string | UnitEnum | null $navigationGroup = 'Master Direktori';
    protected static ?int $navigationSort = 15;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    public static function form(Schema $schema): Schema
    {
        return AutoHarvestQueueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AutoHarvestQueuesTable::configure($table);
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
            'index' => ListAutoHarvestQueues::route('/'),
            'create' => CreateAutoHarvestQueue::route('/create'),
            'edit' => EditAutoHarvestQueue::route('/{record}/edit'),
        ];
    }
}
