<?php

namespace App\Filament\Rahasia\Resources\JobHarvests;

use App\Filament\Rahasia\Resources\JobHarvests\Pages\EditJobHarvest;
use App\Filament\Rahasia\Resources\JobHarvests\Pages\ListJobHarvests;
use App\Filament\Rahasia\Resources\JobHarvests\Tables\JobHarvestsTable;
use App\Filament\Rahasia\Resources\Posts\Schemas\PostForm;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class JobHarvestResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $slug = 'job-harvests';

    protected static ?string $navigationLabel = 'Harvest Loker';
    protected static ?string $modelLabel = 'Lowongan Kerja';
    protected static ?string $pluralModelLabel = 'Harvest Loker';
    protected static string | UnitEnum | null $navigationGroup = 'Master Direktori';
    protected static ?int $navigationSort = 16;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->where('status', 'draft')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->jobs();
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JobHarvestsTable::configure($table);
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
            'index' => ListJobHarvests::route('/'),
            'edit' => EditJobHarvest::route('/{record}/edit'),
        ];
    }
}
