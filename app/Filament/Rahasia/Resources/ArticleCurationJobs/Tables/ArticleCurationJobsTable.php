<?php

namespace App\Filament\Rahasia\Resources\ArticleCurationJobs\Tables;

use App\Jobs\CurateArticleJob;
use App\Models\ArticleCurationJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ArticleCurationJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('city.name')
                    ->label('Kota Target')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('category.name')
                    ->label('Topik / Kategori')
                    ->default('Semua Populer')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('limit')
                    ->label('Jumlah Rekomendasi')
                    ->suffix(' tempat')
                    ->alignCenter(),

                TextColumn::make('article.title')
                    ->label('Artikel Dihasilkan')
                    ->default('-')
                    ->searchable()
                    ->url(fn (ArticleCurationJob $record) => $record->article_id ? route('filament.rahasia.resources.articles.edit', ['record' => $record->article_id]) : null)
                    ->openUrlInNewTab()
                    ->wrap()
                    ->color('primary'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'info',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'processing' => 'Sedang Kurasi AI',
                        'pending' => 'Menunggu Antrean',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),

                TextColumn::make('custom_angle')
                    ->label('Angle Kurasi')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(),

                TextColumn::make('finished_at')
                    ->label('Selesai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M H:i:s')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Keterangan Error')
                    ->limit(35)
                    ->color('danger')
                    ->visible(fn (?ArticleCurationJob $record) => $record?->status === 'failed')
                    ->tooltip(fn (ArticleCurationJob $record) => $record->error_message),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Antrean',
                        'processing' => 'Sedang Kurasi AI',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Jalankan Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (ArticleCurationJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (ArticleCurationJob $record) {
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'finished_at' => null,
                        ]);

                        CurateArticleJob::dispatch(
                            cityId: $record->city_id,
                            categoryId: $record->category_id,
                            limit: $record->limit,
                            customAngle: $record->custom_angle,
                            curationJobId: $record->id
                        );

                        Notification::make()
                            ->title('Job kurasi artikel berhasil dimasukkan kembali ke antrean')
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail Error')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (ArticleCurationJob $record) => !empty($record->error_message))
                    ->modalHeading('Rincian Pesan Error')
                    ->modalDescription(fn (ArticleCurationJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('retryBulk')
                        ->label('Jalankan Ulang Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->accessSelectedRecords()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'pending',
                                    'error_message' => null,
                                    'started_at' => null,
                                    'finished_at' => null,
                                ]);

                                CurateArticleJob::dispatch(
                                    cityId: $record->city_id,
                                    categoryId: $record->category_id,
                                    limit: $record->limit,
                                    customAngle: $record->custom_angle,
                                    curationJobId: $record->id
                                );
                            }

                            Notification::make()
                                ->title('Job terpilih berhasil dimasukkan kembali ke antrean')
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
