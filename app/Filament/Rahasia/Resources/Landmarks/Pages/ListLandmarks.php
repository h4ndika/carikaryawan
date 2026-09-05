<?php

namespace App\Filament\Rahasia\Resources\Landmarks\Pages;

use App\Filament\Rahasia\Resources\Landmarks\LandmarkResource;
use App\Models\City;
use App\Services\LandmarkScraperService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListLandmarks extends ListRecords
{
    protected static string $resource = LandmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scrapeLandmarks')
                ->label('⚡ Auto Scrape / Generate Landmark')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Scrape & Generate Landmark Kota Otomatis')
                ->modalDescription('Pilih metode pencarian, kota target, dan parameter untuk mengumpulkan landmark strategis secara otomatis.')
                ->modalSubmitActionLabel('Mulai Scrape & Simpan')
                ->form([
                    Radio::make('method')
                        ->label('Metode Scraping / Discovery')
                        ->options([
                            'ai' => '🧠 AI Generator (OpenAI GPT) — Sangat Cepat, Akurat, & Lengkap dengan Deskripsi/SEO',
                            'osm' => '🗺️ OpenStreetMap (Overpass API) — Gratis Tanpa Biaya API, Mengambil POI Stasiun, Terminal, Bandara & Mall',
                            'apify' => '📍 Apify Google Places Crawler — Mengambil POI Populer dari Data Google Maps',
                        ])
                        ->default('ai')
                        ->required()
                        ->helperText('Pilih metode ekstraksi landmark yang ingin digunakan.'),

                    Select::make('city_id')
                        ->label('Target Kota / Kabupaten')
                        ->options(function () {
                            return City::query()
                                ->with('province')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (City $c) => [
                                    $c->id => "{$c->name}" . ($c->province ? " ({$c->province->name})" : ''),
                                ]);
                        })
                        ->searchable()
                        ->required()
                        ->helperText('Pilih kota yang ingin dicari/di-generate landmark-nya.'),

                    TextInput::make('limit')
                        ->label('Target Jumlah Landmark')
                        ->numeric()
                        ->default(50)
                        ->minValue(3)
                        ->maxValue(50)
                        ->required()
                        ->helperText('Jumlah landmark yang ingin dikumpulkan (3 - 50).'),

                    CheckboxList::make('types')
                        ->label('Kategori Landmark yang Diutamakan (Opsional)')
                        ->options([
                            'Stasiun Kereta Api & Transit Hub' => 'Stasiun Kereta Api, MRT/LRT/KRL & Whoosh',
                            'Bandara Udara' => 'Bandara Udara (Airport)',
                            'Terminal Bus Utama' => 'Terminal Bus & Angkutan Terpadu',
                            'Wisata Ikonik & Bersejarah' => 'Tempat Wisata & Cagar Budaya Ikonik',
                            'Pusat Perbelanjaan & Mall' => 'Mall & Pusat Perbelanjaan Populer',
                            'Alun-Alun & Taman Kota' => 'Alun-Alun, Lapangan & Ruang Publik Utama',
                            'Universitas Ternama' => 'Kampus / Perguruan Tinggi Terkenal',
                        ])
                        ->columns(2)
                        ->helperText('Centang kategori tertentu untuk memprioritaskan jenis landmark.'),

                    Toggle::make('skip_existing')
                        ->label('Lewati Jika Landmark Sudah Ada')
                        ->default(true)
                        ->helperText('Jika aktif, landmark dengan nama/slug serupa yang sudah ada tidak akan ditimpa.'),
                ])
                ->action(function (array $data, LandmarkScraperService $service): void {
                    $city = City::with('province')->find($data['city_id']);
                    if (!$city) {
                        Notification::make()
                            ->title('Kota tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    $method = $data['method'] ?? 'ai';
                    $limit = (int) ($data['limit'] ?? 15);
                    $types = $data['types'] ?? [];
                    $skipExisting = (bool) ($data['skip_existing'] ?? true);

                    try {
                        // 1. Discover landmarks
                        $discovered = $service->discover($city, $method, [
                            'limit' => $limit,
                            'types' => $types,
                        ]);

                        if (empty($discovered)) {
                            Notification::make()
                                ->title('Tidak Ada Landmark Ditemukan')
                                ->body("Metode [{$method}] tidak menemukan data landmark baru untuk {$city->name}.")
                                ->warning()
                                ->send();
                            return;
                        }

                        // 2. Save into database
                        $stats = $service->saveLandmarks($city, $discovered, $skipExisting);

                        Notification::make()
                            ->title("Scrape Landmark Berhasil ({$city->name})")
                            ->body("Hasil Proses:\n• {$stats['saved']} Landmark Baru Disimpan\n• {$stats['updated']} Diperbarui\n• {$stats['skipped']} Dilewati (Sudah Ada)\n• Total Ditemukan: {$stats['total']}")
                            ->success()
                            ->duration(8000)
                            ->send();
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Gagal Scrape Landmark')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                }),
            CreateAction::make(),
        ];
    }
}
