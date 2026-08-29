<?php

namespace App\Filament\Rahasia\Resources\PhotoScrapeJobs\Tables;

use App\Jobs\ProcessBatchPhotoScrapeJob;
use App\Models\PhotoScrapeJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class PhotoScrapeJobsTable
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

                TextColumn::make('post.title')
                    ->label('Post')
                    ->default(fn (PhotoScrapeJob $record) => $record->post_title ?? '-')
                    ->searchable()
                    ->url(fn (PhotoScrapeJob $record) => $record->post_id ? route('filament.rahasia.resources.posts.edit', ['record' => $record->post_id]) : null)
                    ->openUrlInNewTab()
                    ->wrap(),

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
                        'processing' => 'Sedang Proses',
                        'pending' => 'Menunggu Antrean',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),

                TextColumn::make('photos_count')
                    ->label('Foto Didapat')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('limit')
                    ->label('Target Foto')
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('publish_on_complete')
                    ->label('Auto Publish')
                    ->boolean()
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
                    ->label('Keterangan / Error')
                    ->limit(30)
                    ->color('danger')
                    ->visible(fn (?PhotoScrapeJob $record) => !empty($record?->error_message))
                    ->tooltip(fn (PhotoScrapeJob $record) => $record->error_message),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Antrean',
                        'processing' => 'Sedang Proses',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Jalankan Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (PhotoScrapeJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (PhotoScrapeJob $record) {
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'finished_at' => null,
                        ]);

                        ProcessBatchPhotoScrapeJob::dispatch([$record->id]);

                        Notification::make()
                            ->title('Job berhasil dimasukkan kembali ke antrean')
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (PhotoScrapeJob $record) => !empty($record->error_message))
                    ->modalHeading('Rincian Error / Keterangan')
                    ->modalDescription(fn (PhotoScrapeJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('processBatchSelected')
                        ->label('Proses Terpilih (Batch)')
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Proses Antrean Terpilih')
                        ->modalDescription('Akan memasukkan semua job yang dipilih ke background worker.')
                        ->action(function (Collection $records) {
                            $jobIds = $records->pluck('id')->all();
                            $count = count($jobIds);

                            foreach (array_chunk($jobIds, 25) as $chunkIds) {
                                ProcessBatchPhotoScrapeJob::dispatch($chunkIds);
                            }

                            Notification::make()
                                ->title('Job Berhasil Dimasukkan ke Antrean')
                                ->body("{$count} job terpilih dimasukkan ke antrean worker.")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
