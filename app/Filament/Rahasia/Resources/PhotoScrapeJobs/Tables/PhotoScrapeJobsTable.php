<?php

namespace App\Filament\Rahasia\Resources\PhotoScrapeJobs\Tables;

use App\Models\PhotoScrapeJob;
use App\Services\PhotoScrapeQueue;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

                TextColumn::make('phase')
                    ->label('Tahap')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                        'queued' => 'Menunggu worker/slot',
                        'preparing' => 'Validasi tempat',
                        'starting' => 'Memulai Apify',
                        'polling' => 'Menunggu hasil Apify',
                        default => $state ?? '-',
                    })
                    ->toggleable(),
                TextColumn::make('apify_run_id')
                    ->label('Run Apify')->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deadline_at')
                    ->label('Batas Waktu')->dateTime('d M H:i:s')->toggleable(),

                TextColumn::make('photos_count')
                    ->label('Foto Didapat')
                    ->badge()
                    ->color(fn (int $state): string => $state > 1 ? 'success' : ($state === 1 ? 'warning' : 'danger')),

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
                    ->placeholder('-')
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

                Filter::make('photos_count')
                    ->form([
                        Select::make('photos_range')
                            ->label('Foto Didapat')
                            ->options([
                                'lte_1' => '<= 1 Foto (Kurang / Gagal)',
                                'gt_1' => '> 1 Foto (Lengkap / Berhasil)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ($data['photos_range'] ?? null) === 'lte_1',
                                fn (Builder $q) => $q->where('photos_count', '<=', 1)
                            )
                            ->when(
                                ($data['photos_range'] ?? null) === 'gt_1',
                                fn (Builder $q) => $q->where('photos_count', '>', 1)
                            );
                    }),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Jalankan Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (PhotoScrapeJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (PhotoScrapeJob $record) {
                        $count = app(PhotoScrapeQueue::class)->enqueue([$record->id]);

                        Notification::make()
                            ->title($count ? 'Job dimasukkan kembali ke antrean' : 'Job sudah aktif; tidak diduplikasi')
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (PhotoScrapeJob $record) => ! empty($record->error_message))
                    ->modalHeading('Rincian Error / Keterangan')
                    ->modalDescription(fn (PhotoScrapeJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make()->visible(fn (PhotoScrapeJob $record) => ! in_array($record->status, ['pending', 'processing'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('processBatchSelected')
                        ->label('Proses Terpilih (Batch)')
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Proses Antrean Terpilih')
                        ->modalDescription('Akan memasukkan semua job yang dipilih ke background worker.')
                        ->action(function (Collection $records) {
                            $jobIds = $records->pluck('id')->all();
                            $count = count($jobIds);

                            if ($count === 0) {
                                return;
                            }

                            $count = app(PhotoScrapeQueue::class)->enqueue($jobIds);

                            Notification::make()
                                ->title('Job Berhasil Dimasukkan ke Antrean')
                                ->body("{$count} job dimasukkan ke worker. Job yang masih aktif dilewati.")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->fetchSelectedRecords(true)
                        ->action(fn (Collection $records) => PhotoScrapeJob::whereIn('id', $records->modelKeys())
                            ->whereIn('status', ['completed', 'failed'])->delete()),
                ]),
            ]);
    }
}
