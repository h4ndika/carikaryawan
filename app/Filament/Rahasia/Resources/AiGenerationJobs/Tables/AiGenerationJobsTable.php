<?php

namespace App\Filament\Rahasia\Resources\AiGenerationJobs\Tables;

use App\Jobs\GeneratePostContentJob;
use App\Models\AiGenerationJob;
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

class AiGenerationJobsTable
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
                    ->default(fn (AiGenerationJob $record) => $record->post_title ?? '-')
                    ->searchable()
                    ->url(fn (AiGenerationJob $record) => $record->post_id ? route('filament.rahasia.resources.posts.edit', ['record' => $record->post_id]) : null)
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

                TextColumn::make('prompt_additional')
                    ->label('Prompt Tambahan')
                    ->limit(25)
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
                    ->label('Keterangan Error')
                    ->limit(30)
                    ->color('danger')
                    ->visible(fn (?AiGenerationJob $record) => $record?->status === 'failed')
                    ->tooltip(fn (AiGenerationJob $record) => $record->error_message),
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
                    ->visible(fn (AiGenerationJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (AiGenerationJob $record) {
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'finished_at' => null,
                        ]);

                        GeneratePostContentJob::dispatch($record->id);

                        try {
                            \Illuminate\Support\Facades\Process::path(base_path())->start('php artisan ai:process-batch --limit=10 --concurrency=5');
                        } catch (\Throwable $e) {
                            // Fallback
                        }

                        Notification::make()
                            ->title('Job berhasil dimasukkan kembali ke antrean')
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail Error')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (AiGenerationJob $record) => !empty($record->error_message))
                    ->modalHeading('Rincian Pesan Error')
                    ->modalDescription(fn (AiGenerationJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('processBatchSelected')
                        ->label('Proses Terpilih (Paralel)')
                        ->icon('heroicon-o-bolt')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Proses Terpilih Secara Bersamaan')
                        ->modalDescription('Akan memproses semua job yang dipilih secara paralel di latar belakang.')
                        ->action(function (Collection $records) {
                            $jobIds = $records->pluck('id')->all();
                            $count = count($jobIds);

                            // 1. Dispatch ke Laravel Queue (untuk php artisan queue:work)
                            foreach (array_chunk($jobIds, 10) as $chunkIds) {
                                \App\Jobs\ProcessBatchAiQueueJob::dispatch($chunkIds, 5);
                            }

                            // 2. Spawn background worker mandiri
                            $php = defined('PHP_BINARY') && PHP_BINARY ? escapeshellarg(PHP_BINARY) : 'php';
                            $artisan = escapeshellarg(base_path('artisan'));

                            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                @pclose(@popen("start /B {$php} {$artisan} ai:process-batch --limit={$count} --concurrency=5 > NUL 2>&1", 'r'));
                            } else {
                                @exec("{$php} {$artisan} ai:process-batch --limit={$count} --concurrency=5 > /dev/null 2>&1 &");
                            }

                            Notification::make()
                                ->title('Proses Dimulai di Latar Belakang')
                                ->body("Sedang memproses {$count} job terpilih secara paralel. Status akan ter-update otomatis.")
                                ->success()
                                ->send();
                        }),

                    Action::make('retryBulk')
                        ->label('Jalankan Ulang Terpilih (Kembalikan ke Antrean)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->accessSelectedRecords()
                        ->action(function (Collection $records) {
                            $jobIds = [];
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'pending',
                                    'error_message' => null,
                                    'started_at' => null,
                                    'finished_at' => null,
                                ]);
                                $jobIds[] = $record->id;
                            }

                            $count = count($jobIds);
                            if ($count > 0) {
                                foreach (array_chunk($jobIds, 10) as $chunkIds) {
                                    \App\Jobs\ProcessBatchAiQueueJob::dispatch($chunkIds, 5);
                                }

                                $php = defined('PHP_BINARY') && PHP_BINARY ? escapeshellarg(PHP_BINARY) : 'php';
                                $artisan = escapeshellarg(base_path('artisan'));

                                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                    @pclose(@popen("start /B {$php} {$artisan} ai:process-batch --limit={$count} --concurrency=5 > NUL 2>&1", 'r'));
                                } else {
                                    @exec("{$php} {$artisan} ai:process-batch --limit={$count} --concurrency=5 > /dev/null 2>&1 &");
                                }
                            }

                            Notification::make()
                                ->title("$count Job berhasil dimasukkan kembali ke antrean")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
