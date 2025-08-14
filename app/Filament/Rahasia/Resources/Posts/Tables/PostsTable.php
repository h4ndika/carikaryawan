<?php

namespace App\Filament\Rahasia\Resources\Posts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('excerpt')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => strlen($state) > 25 ? mb_substr($state, 0, 25) . '...' : $state),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                TextColumn::make('city.name')
                    ->label('City')
                    ->searchable(),
                TextColumn::make('province.name')
                    ->label('Province')
                    ->searchable(),
                TextColumn::make('status'),
            ])
            ->filters([
                TrashedFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),
                \Filament\Tables\Filters\SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->label('City'),
                \Filament\Tables\Filters\SelectFilter::make('province_id')
                    ->relationship('province', 'name')
                    ->label('Province'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null);
    }
}
