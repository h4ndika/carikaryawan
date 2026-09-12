<?php

namespace App\Filament\Rahasia\Resources\AutoHarvestQueues\Pages;

use App\Filament\Rahasia\Resources\AutoHarvestQueues\AutoHarvestQueueResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAutoHarvestQueues extends ListRecords
{
    protected static string $resource = AutoHarvestQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('triggerScrape')
                ->label('Scrape 300 Tempat Sekarang')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Auto-Scrape Google Maps')
                ->modalDescription('Sistem akan memilih kota yang paling minim post dan mengikis data tempat Google Maps untuk disimpan ke antrean stok.')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::queue('maps:auto-harvest', ['--force' => true, '--limit' => 300]);
                    \Filament\Notifications\Notification::make()
                        ->title('Scrape Dimulai')
                        ->body('Proses scrape Google Maps 300 data sedang berjalan di latar belakang.')
                        ->success()
                        ->send();
                }),

            \Filament\Actions\Action::make('triggerDripPublish')
                ->label('Publish 50 Post Sekarang')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Drip Publish')
                ->modalDescription('Sistem akan mengambil 50 tempat draft dari antrean stok ini, men-generate ulasan AI secara paralel, dan mempublikasikannya.')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::queue('maps:drip-publish', ['--limit' => 50, '--concurrency' => 10]);
                    \Filament\Notifications\Notification::make()
                        ->title('Drip Publish Dimulai')
                        ->body('50 post sedang diproses AI secara paralel di latar belakang dan akan segera terbit.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
