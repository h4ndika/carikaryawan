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
            Action::make('runWorker')
                ->label('Proses Antrean Sekarang')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Worker Queue')
                ->modalDescription('Akan memproses antrean job AI yang sedang menunggu (pending) di server.')
                ->action(function () {
                    try {
                        // Process pending jobs with queue:work --stop-when-empty
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
