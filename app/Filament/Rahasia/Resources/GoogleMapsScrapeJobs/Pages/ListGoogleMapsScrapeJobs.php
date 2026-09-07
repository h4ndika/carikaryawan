<?php

namespace App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\Pages;

use App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\GoogleMapsScrapeJobResource;
use App\Jobs\RunGoogleMapsScrapeJob;
use App\Models\ApifyAccount;
use App\Models\Category;
use App\Models\City;
use App\Models\GoogleMapsScrapeJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;

class ListGoogleMapsScrapeJobs extends ListRecords
{
    protected static string $resource = GoogleMapsScrapeJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startScrape')
                ->label('Mulai Ekstraksi Data (Maps / Loker)')
                ->icon('heroicon-o-magnifying-glass-plus')
                ->color('primary')
                ->modalHeading('Ekstraksi Tempat & Lowongan Kerja')
                ->modalDescription('Pilih mesin scraper dan masukkan kata kunci. Data akan otomatis diekstrak via Apify dan tersimpan ke database.')
                ->modalSubmitActionLabel('Mulai Ekstraksi & Simpan')
                ->form([
                    Select::make('engine')
                        ->label('Pilihan Mesin Scraper')
                        ->options([
                            'google-jobs' => 'Google Jobs (spidersource/google-jobs-scraper) — Default Loker',
                            'instagram' => 'Instagram Loker (apify/instagram-post-scraper) — Akun Komunitas',
                            'google-maps' => 'Google Maps (compass/crawler-google-places) — Tempat & Fasilitas',
                        ])
                        ->default('google-jobs')
                        ->required()
                        ->live()
                        ->columnSpanFull()
                        ->helperText('Pilih mesin pencari sesuai kebutuhan: Google Jobs untuk loker terstruktur, Instagram untuk akun loker daerah, atau Google Maps untuk tempat.'),

                    TextInput::make('query')
                        ->label(fn (\Filament\Forms\Get $get) => match ($get('engine')) {
                            'instagram' => 'Username Akun Instagram (tanpa @)',
                            'google-jobs' => 'Kata Kunci Lowongan Kerja',
                            default => 'Kata Kunci Tempat Google Maps'
                        })
                        ->placeholder(fn (\Filament\Forms\Get $get) => match ($get('engine')) {
                            'instagram' => 'Contoh: lokerbandung, infolokerjogja, loker.surabaya',
                            'google-jobs' => 'Contoh: lowongan barista bandung, staf kafe jogja, cook jakarta',
                            default => 'Contoh: Hotel di Bandung, Kafe di Jogja, Penitipan koper dekat Stasiun Bandung'
                        })
                        ->required()
                        ->columnSpanFull()
                        ->helperText(fn (\Filament\Forms\Get $get) => match ($get('engine')) {
                            'instagram' => 'Ketik username akun Instagram penyedia info loker tanpa simbol @.',
                            'google-jobs' => 'Ketik posisi dan kota tujuan (misal: "lowongan barista bandung").',
                            default => 'Ketik kata kunci tempat atau fasilitas transit di sekitar stasiun/bandara.'
                        }),

                    TextInput::make('location')
                        ->label('Lokasi / Kota Spesifik (Opsional)')
                        ->placeholder('Contoh: Bandung, Indonesia')
                        ->visible(fn (\Filament\Forms\Get $get) => $get('engine') === 'google-maps')
                        ->columnSpanFull(),

                    Select::make('category_id')
                        ->label('Tetapkan Kategori (Opsional)')
                        ->options(Category::pluck('name', 'id'))
                        ->searchable()
                        ->default(fn (\Filament\Forms\Get $get) => in_array($get('engine'), ['google-jobs', 'instagram']) 
                            ? Category::where('slug', 'lowongan-kerja')->value('id') 
                            : null
                        )
                        ->placeholder('Otomatis deteksi dari sumber')
                        ->helperText('Jika dikosongkan, kategori akan dideteksi otomatis.'),

                    Select::make('city_id')
                        ->label('Tetapkan Kota (Opsional)')
                        ->options(City::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Otomatis deteksi dari Lokasi/Alamat')
                        ->helperText('Jika dikosongkan, kota akan dideteksi dari hasil parsing.'),

                    TextInput::make('target_limit')
                        ->label('Jumlah Target Data')
                        ->numeric()
                        ->default(20)
                        ->minValue(1)
                        ->maxValue(500)
                        ->required(),

                    Select::make('apify_account_id')
                        ->label('Pilih Akun / Token Apify')
                        ->options(
                            ApifyAccount::active()->pluck('label', 'id')->map(function ($label, $id) {
                                return $label ?: 'Akun #' . $id;
                            })
                        )
                        ->placeholder('Rotasi Otomatis (Load Balancing)')
                        ->helperText('Pilih akun tertentu atau biarkan otomatis memilih akun yang siap pakai.'),

                    Toggle::make('include_images')
                        ->label('Ambil Foto / Gambar')
                        ->default(true)
                        ->visible(fn (\Filament\Forms\Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('include_reviews')
                        ->label('Ambil Ulasan Pengguna')
                        ->default(false)
                        ->visible(fn (\Filament\Forms\Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('publish_imported')
                        ->label('Langsung Publikasikan')
                        ->default(false)
                        ->helperText('Jika aktif, status langsung Published. Jika tidak, akan Draft.'),
                ])
                ->action(function (array $data) {
                    $job = GoogleMapsScrapeJob::create([
                        'engine' => $data['engine'] ?? 'google-jobs',
                        'query' => $data['query'],
                        'location' => $data['location'] ?? null,
                        'category_id' => $data['category_id'] ?? null,
                        'city_id' => $data['city_id'] ?? null,
                        'target_limit' => (int) ($data['target_limit'] ?? 20),
                        'include_images' => (bool) ($data['include_images'] ?? true),
                        'include_reviews' => (bool) ($data['include_reviews'] ?? false),
                        'publish_imported' => (bool) ($data['publish_imported'] ?? false),
                        'apify_account_id' => $data['apify_account_id'] ?? null,
                        'status' => 'pending',
                    ]);

                    RunGoogleMapsScrapeJob::dispatch($job->id);

                    Notification::make()
                        ->title('Ekstraksi Google Maps berhasil dimulai!')
                        ->body("Pencarian '{$job->query}' telah masuk ke antrean background.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
