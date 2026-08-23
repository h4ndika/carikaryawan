<?php

namespace App\Filament\Rahasia\Resources\AiGenerationJobs\Pages;

use App\Filament\Rahasia\Resources\AiGenerationJobs\AiGenerationJobResource;
use App\Models\AiGenerationJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Artisan;

class ListAiGenerationJobs extends ListRecords
{
    protected static string $resource = AiGenerationJobResource::class;

    public function getTabs(): array
    {
        $pendingCount = AiGenerationJob::whereIn('status', ['pending', 'processing'])->count();
        $failedCount = AiGenerationJob::where('status', 'failed')->count();

        return [
            'all' => Tab::make('Semua'),
            'pending' => Tab::make('Antrean Aktif')
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', ['pending', 'processing']))
                ->badge($pendingCount > 0 ? (string) $pendingCount : null)
                ->badgeColor('warning'),
            'completed' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed')),
            'failed' => Tab::make('Gagal')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'failed'))
                ->badge($failedCount > 0 ? (string) $failedCount : null)
                ->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runBatchConcurrent')
                ->label('Proses Batch Cepat (Paralel)')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->modalHeading('Proses Antrean AI Secara Bersamaan')
                ->modalDescription('Memproses beberapa post sekaligus secara paralel (5-10 post bersamaan) menggunakan multi-thread API call untuk kecepatan maksimal.')
                ->form([
                    \Filament\Forms\Components\Select::make('limit')
                        ->label('Jumlah Antrean yang Diproses')
                        ->options([
                            '5' => '5 Post',
                            '10' => '10 Post (Rekomendasi)',
                            '20' => '20 Post',
                            '50' => '50 Post',
                        ])
                        ->default('10')
                        ->required(),
                    \Filament\Forms\Components\Select::make('concurrency')
                        ->label('Kecepatan Paralel (Simultan)')
                        ->options([
                            '5' => '5 Post Sekaligus',
                            '10' => '10 Post Sekaligus (Cepat / Rekomendasi)',
                            '15' => '15 Post Sekaligus (Sangat Cepat)',
                        ])
                        ->default('10')
                        ->required()
                        ->helperText('Jumlah post yang dikirim ke AI dalam satu detik yang sama.'),
                ])
                ->action(function (array $data) {
                    $limit = (int) ($data['limit'] ?? 10);
                    $concurrency = (int) ($data['concurrency'] ?? 10);

                    $pendingJobs = AiGenerationJob::where('status', 'pending')
                        ->orderBy('id', 'asc')
                        ->limit($limit)
                        ->pluck('id')
                        ->all();

                    $count = count($pendingJobs);

                    if ($count === 0) {
                        Notification::make()
                            ->title('Tidak ada antrean pending')
                            ->body('Semua antrean sudah selesai diproses.')
                            ->info()
                            ->send();
                        return;
                    }

                    // Dispatch ke Laravel Queue Table (diproses oleh php artisan queue:work)
                    foreach (array_chunk($pendingJobs, 10) as $chunkIds) {
                        \App\Jobs\ProcessBatchAiQueueJob::dispatch($chunkIds, $concurrency);
                    }

                    Notification::make()
                        ->title('Antrean Batch Berhasil Dibuat')
                        ->body("{$count} antrean AI siap diproses secara paralel ({$concurrency} post sekaligus). Pastikan worker queue berjalan (php artisan queue:work).")
                        ->success()
                        ->send();
                }),

            Action::make('runWorker')
                ->label('Worker Standar (1 per 1)')
                ->icon('heroicon-o-play')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Worker Standar')
                ->modalDescription('Akan memproses antrean job AI satu per satu menggunakan artisan queue:work.')
                ->action(function () {
                    try {
                        Artisan::call('queue:work', [
                            '--stop-when-empty' => true,
                            '--timeout' => 180,
                            '--tries' => 2,
                        ]);

                        Notification::make()
                            ->title('Worker Queue Selesai Berjalan')
                            ->body('Job dalam antrean telah diproses.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal menjalankan worker')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('clearCompleted')
                ->label('Bersihkan Riwayat Selesai')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    $deleted = AiGenerationJob::where('status', 'completed')->delete();

                    Notification::make()
                        ->title("{$deleted} riwayat job selesai dibersihkan")
                        ->success()
                        ->send();
                }),
        ];
    }
}
