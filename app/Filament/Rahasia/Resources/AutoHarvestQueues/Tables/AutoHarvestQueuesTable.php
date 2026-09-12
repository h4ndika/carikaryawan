<?php

namespace App\Filament\Rahasia\Resources\AutoHarvestQueues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class AutoHarvestQueuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('post.title')
                    ->label('Nama Tempat')
                    ->searchable()
                    ->wrap()
                    ->weight('bold'),

                \Filament\Tables\Columns\TextColumn::make('city.name')
                    ->label('Kota')
                    ->sortable()
                    ->badge(),

                \Filament\Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'processing' => 'warning',
                        'pending' => 'gray',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),

                \Filament\Tables\Columns\TextColumn::make('published_at')
                    ->label('Waktu Terbit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending (Menunggu Jadwal)',
                        'processing' => 'Sedang Diproses AI',
                        'published' => 'Sudah Diterbitkan',
                        'failed' => 'Gagal',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('city_id')
                    ->label('Kota')
                    ->relationship('city', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('publishNow')
                    ->label('Publish Sekarang')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record, \App\Services\PostAiContentService $aiService) {
                        try {
                            $post = $record->post;
                            if (!$post) {
                                throw new \Exception('Data post tidak ditemukan.');
                            }
                            $aiService->generateAndSaveContent($post, true);
                            $record->update([
                                'status' => 'published',
                                'published_at' => now(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil Diterbitkan')
                                ->body("Post '{$post->title}' berhasil dibuat kontennya dan diterbitkan.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Gagal Menerbitkan')
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
                ]),
            ]);
    }
}
