<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts;

use App\Filament\Rahasia\Resources\ApifyAccounts\Pages\CreateApifyAccount;
use App\Filament\Rahasia\Resources\ApifyAccounts\Pages\EditApifyAccount;
use App\Filament\Rahasia\Resources\ApifyAccounts\Pages\ListApifyAccounts;
use App\Filament\Rahasia\Resources\ApifyAccounts\Schemas\ApifyAccountForm;
use App\Filament\Rahasia\Resources\ApifyAccounts\Tables\ApifyAccountsTable;
use App\Models\ApifyAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApifyAccountResource extends Resource
{
    protected static ?string $model = ApifyAccount::class;

    protected static string | UnitEnum | null $navigationGroup = 'Google Maps Extractor';

    protected static ?string $navigationLabel = 'Akun Apify';

    protected static ?string $modelLabel = 'Akun Apify';

    protected static ?string $pluralModelLabel = 'Akun Apify (Multi-Token)';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ApifyAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApifyAccountsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $active = static::getModel()::active()->count();
        return $active > 0 ? "{$active} Aktif" : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApifyAccounts::route('/'),
            'create' => CreateApifyAccount::route('/create'),
            'edit' => EditApifyAccount::route('/{record}/edit'),
        ];
    }
}
