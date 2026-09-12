<?php

namespace App\Filament\Rahasia\Resources\Articles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->wrap()
                    ->description(fn ($record) => $record->excerpt),

                TextColumn::make('city.name')
                    ->label('Kota')
                    ->sortable()
                    ->badge(),

                TextColumn::make('category.name')
                    ->label('Topik')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        default => 'warning',
                    }),

                TextColumn::make('views_count')
                    ->label('Dibaca')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Tanggal Publikasi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                \Filament\Tables\Columns\IconColumn::make('posted_to_threads_at')
                    ->label('Threads')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->posted_to_threads_at ? 'Terbit di Threads: ' . $record->posted_to_threads_at->format('d M Y H:i') : 'Belum di-share ke Threads')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('shareToThreads')
                    ->label('Kirim ke Threads')
                    ->icon('heroicon-o-share')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Bagikan Artikel ke Threads')
                    ->modalDescription('Artikel ini akan langsung diposting ke linimasa resmi Threads @disekitarcom.')
                    ->visible(fn ($record) => $record->status === 'published')
                    ->action(function ($record, \App\Services\ThreadsService $threadsService) {
                        try {
                            $res = $threadsService->publishArticle($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil Terbit di Threads!')
                                ->body("Artikel '{$record->title}' berhasil dibagikan ke Threads.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Kirim ke Threads')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
