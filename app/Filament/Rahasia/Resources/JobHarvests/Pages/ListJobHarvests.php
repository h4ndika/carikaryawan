<?php

namespace App\Filament\Rahasia\Resources\JobHarvests\Pages;

use App\Filament\Rahasia\Resources\JobHarvests\JobHarvestResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListJobHarvests extends ListRecords
{
    protected static string $resource = JobHarvestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('triggerHarvest')
                ->label('Harvest Loker Sekarang')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->modalHeading('Jalankan Auto-Harvest Lowongan Kerja')
                ->modalDescription('Sistem akan mengekstrak lowongan kerja terbaru, membuat draf, otomatis menghasilkan konten dengan AI, lalu menerbitkannya.')
                ->modalSubmitActionLabel('Mulai Harvest Loker')
                ->form([
                    Select::make('engine')
                        ->label('Scraper Engine')
                        ->options([
                            'auto' => 'Auto (Rotasi Selang-seling Glints <-> Jobstreet)',
                            'glints' => 'Glints (Spesifik)',
                            'jobstreet' => 'Jobstreet (Spesifik)',
                        ])
                        ->default('auto')
                        ->required(),

                    TextInput::make('limit')
                        ->label('Jumlah Lowongan')
                        ->numeric()
                        ->default(20)
                        ->minValue(5)
                        ->maxValue(100)
                        ->required()
                        ->helperText('Berapa banyak loker yang ingin diambil dalam eksekusi ini.'),

                    Select::make('keyword')
                        ->label('Kata Kunci (Opsional)')
                        ->options([
                            'Staff Admin' => 'Staff Admin',
                            'Customer Service' => 'Customer Service',
                            'Staff Keuangan Finance Accounting' => 'Staff Keuangan Finance Accounting',
                            'Marketing Digital Sales' => 'Marketing Digital Sales',
                            'Graphic Designer' => 'Graphic Designer',
                            'Content Creator Social Media' => 'Content Creator Social Media',
                            'Barista Resto F&B' => 'Barista Resto F&B',
                            'Kasir Store Crew' => 'Kasir Store Crew',
                            'Warehouse Staff Gudang' => 'Warehouse Staff Gudang',
                            'Operator Produksi' => 'Operator Produksi',
                            'Human Resources HRD' => 'Human Resources HRD',
                            'IT Support Technician' => 'IT Support Technician',
                        ])
                        ->searchable()
                        ->placeholder('Biarkan kosong untuk rotasi otomatis harian')
                        ->helperText('Kosongkan bila ingin menggunakan jadwal rotasi kata kunci harian.'),

                    Select::make('city')
                        ->label('Kota Tujuan (Opsional)')
                        ->options([
                            'Jakarta' => 'Jakarta',
                            'Surabaya' => 'Surabaya',
                            'Bandung' => 'Bandung',
                            'Medan' => 'Medan',
                            'Semarang' => 'Semarang',
                            'Yogyakarta' => 'Yogyakarta',
                            'Denpasar' => 'Denpasar',
                            'Makassar' => 'Makassar',
                            'Bekasi' => 'Bekasi',
                            'Tangerang' => 'Tangerang',
                            'Depok' => 'Depok',
                            'Bogor' => 'Bogor',
                            'Malang' => 'Malang',
                            'Batam' => 'Batam',
                            'Balikpapan' => 'Balikpapan',
                            'Palembang' => 'Palembang',
                        ])
                        ->searchable()
                        ->placeholder('Biarkan kosong untuk rotasi otomatis harian')
                        ->helperText('Kosongkan bila ingin menggunakan rotasi kota prioritas harian.'),
                ])
                ->action(function (array $data): void {
                    $params = [
                        '--limit' => (int) ($data['limit'] ?? 20),
                        '--engine' => $data['engine'] ?? 'glints',
                    ];

                    if (!empty($data['keyword'])) {
                        $params['--keyword'] = $data['keyword'];
                    }

                    if (!empty($data['city'])) {
                        $params['--city'] = $data['city'];
                    }

                    Artisan::queue('jobs:auto-harvest', $params);

                    Notification::make()
                        ->title('Harvest Loker Dijadwalkan')
                        ->body('Proses harvest loker telah dikirim ke worker latar belakang. Data baru akan otomatis masuk dan diproses AI.')
                        ->success()
                        ->send();
                }),

            Action::make('viewAiQueue')
                ->label('Antrean AI')
                ->icon('heroicon-o-sparkles')
                ->color('amber')
                ->url(route('filament.rahasia.resources.ai-generation-jobs.index')),
        ];
    }
}
