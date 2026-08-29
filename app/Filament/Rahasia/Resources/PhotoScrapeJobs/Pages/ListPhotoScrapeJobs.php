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
                ->modalDescription('Memproses post dalam antrean pending menggunakan background job.')
                ->form([
                    Select::make('limit')
                        ->label('Jumlah Antrean yang Diproses')
                        ->options([
                            '10' => '10 Post',
                            '25' => '25 Post (Rekomendasi)',
                            '50' => '50 Post',
                            '100' => '100 Post',
                        ])
                        ->default('25')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $limit = (int) ($data['limit'] ?? 25);
                    $jobIds = PhotoScrapeJob::where('status', 'pending')
                        ->limit($limit)
                        ->pluck('id')
                        ->all();

                    if (empty($jobIds)) {
                        Notification::make()
                            ->title('Tidak Ada Antrean')
                            ->body('Tidak ada job scrape foto dengan status pending.')
                            ->warning()
                            ->send();
                        return;
                    }

                    foreach (array_chunk($jobIds, 25) as $chunkIds) {
                        ProcessBatchPhotoScrapeJob::dispatch($chunkIds);
                    }

                    Notification::make()
                        ->title('Antrean Berhasil Dijalankan')
                        ->body(count($jobIds) . ' antrean foto telah dimasukkan ke background worker.')
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
