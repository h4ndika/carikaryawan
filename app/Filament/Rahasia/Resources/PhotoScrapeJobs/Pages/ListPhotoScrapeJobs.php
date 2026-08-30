<?php

namespace App\Filament\Rahasia\Resources\PhotoScrapeJobs\Pages;

use App\Filament\Rahasia\Resources\PhotoScrapeJobs\PhotoScrapeJobResource;
use App\Jobs\ProcessBatchPhotoScrapeJob;
use App\Models\PhotoScrapeJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListPhotoScrapeJobs extends ListRecords
{
    protected static string $resource = PhotoScrapeJobResource::class;

    public function getTabs(): array
    {
        $pendingCount = PhotoScrapeJob::whereIn('status', ['pending', 'processing'])->count();
        $failedCount = PhotoScrapeJob::where('status', 'failed')->count();

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
            Action::make('runPendingBatch')
                ->label('Proses Antrean Sekarang')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->modalHeading('Jalankan Antrean Scrape Foto')
                ->modalDescription('Memproses job scrape foto dalam antrean menggunakan background worker.')
                ->form([
                    Select::make('target_status')
                        ->label('Status yang Diproses')
                        ->options([
                            'all_unfinished' => 'Pending & Gagal (Ulangi yang Belum Berhasil)',
                            'pending' => 'Hanya Status Pending',
                            'failed' => 'Hanya Status Gagal',
                        ])
                        ->default('all_unfinished')
                        ->required(),
                    Select::make('limit')
                        ->label('Jumlah Antrean yang Diproses')
                        ->options([
                            '10' => '10 Post',
                            '25' => '25 Post (Rekomendasi)',
                            '50' => '50 Post',
                            '100' => '100 Post',
                            '250' => '250 Post',
                        ])
                        ->default('25')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $limit = (int) ($data['limit'] ?? 25);
                    $targetStatus = $data['target_status'] ?? 'all_unfinished';

                    $query = PhotoScrapeJob::query();
                    if ($targetStatus === 'pending') {
                        $query->where('status', 'pending');
                    } elseif ($targetStatus === 'failed') {
                        $query->where('status', 'failed');
                    } else {
                        $query->whereIn('status', ['pending', 'failed', 'processing']);
                    }

                    $jobIds = $query->limit($limit)->pluck('id')->all();

                    if (empty($jobIds)) {
                        Notification::make()
                            ->title('Tidak Ada Antrean')
                            ->body('Tidak ditemukan job dengan status yang dipilih.')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Reset all selected jobs to pending status and clear errors
                    PhotoScrapeJob::whereIn('id', $jobIds)->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'started_at' => null,
                        'finished_at' => null,
                    ]);

                    foreach (array_chunk($jobIds, 25) as $chunkIds) {
                        ProcessBatchPhotoScrapeJob::dispatch($chunkIds);
                    }

                    Notification::make()
                        ->title('Antrean Berhasil Dijalankan')
                        ->body(count($jobIds) . ' antrean foto telah dikembalikan ke status pending dan dimasukkan ke worker.')
                        ->success()
                        ->send();
                }),

            Action::make('retryAllFailed')
                ->label('Ulangi Semua Gagal')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Ulangi Semua Job Gagal')
                ->modalDescription('Apakah Anda yakin ingin mengembalikan semua job yang berstatus Gagal menjadi Pending dan memprosesnya?')
                ->modalSubmitActionLabel('Ya, Ulangi Semua Gagal')
                ->action(function () {
                    $failedJobIds = PhotoScrapeJob::where('status', 'failed')->pluck('id')->all();

                    if (empty($failedJobIds)) {
                        Notification::make()
                            ->title('Tidak Ada Job Gagal')
                            ->body('Semua job sudah selesai atau tidak ada yang berstatus gagal.')
                            ->info()
                            ->send();
                        return;
                    }

                    PhotoScrapeJob::whereIn('id', $failedJobIds)->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'started_at' => null,
                        'finished_at' => null,
                    ]);

                    foreach (array_chunk($failedJobIds, 25) as $chunkIds) {
                        ProcessBatchPhotoScrapeJob::dispatch($chunkIds);
                    }

                    Notification::make()
                        ->title('Job Gagal Berhasil Di-queue Ulang')
                        ->body(count($failedJobIds) . ' job gagal telah diubah statusnya menjadi pending dan dimasukkan ke worker.')
                        ->success()
                        ->send();
                }),

            Action::make('clearCompleted')
                ->label('Bersihkan Riwayat Selesai')
                ->icon('heroicon-o-trash')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Bersihkan Riwayat Job Selesai')
                ->modalDescription('Apakah Anda yakin ingin menghapus semua data antrean yang statusnya Selesai?')
                ->modalSubmitActionLabel('Ya, Hapus Selesai')
                ->action(function () {
                    $deleted = PhotoScrapeJob::where('status', 'completed')->delete();

                    Notification::make()
                        ->title("{$deleted} riwayat antrean foto selesai berhasil dibersihkan")
                        ->success()
                        ->send();
                }),
        ];
    }
}
