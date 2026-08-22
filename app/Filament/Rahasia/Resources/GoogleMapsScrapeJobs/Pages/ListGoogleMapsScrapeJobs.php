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
                ->label('Mulai Ekstraksi Google Maps')
                ->icon('heroicon-o-magnifying-glass-plus')
                ->color('primary')
                ->modalHeading('Ekstraksi Tempat dari Google Maps')
                ->modalDescription('Masukkan kata kunci pencarian. Data tempat (nama, rating, koordinat, foto, kontak) akan otomatis diekstrak dan disimpan ke database.')
                ->modalSubmitActionLabel('Mulai Ekstraksi & Simpan')
                ->form([
                    TextInput::make('query')
                        ->label('Kata Kunci Pencarian Google Maps')
                        ->placeholder('Contoh: Hotel di Bandung, Kafe Instagramable di Jogja')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Sama persis seperti yang Anda ketik di kolom pencarian Google Maps.'),

                    TextInput::make('location')
                        ->label('Lokasi / Kota Spesifik (Opsional)')
                        ->placeholder('Contoh: Bandung, Indonesia')
                        ->columnSpanFull(),

                    Select::make('category_id')
                        ->label('Tetapkan Kategori (Opsional)')
                        ->options(Category::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Otomatis deteksi dari Google Maps')
                        ->helperText('Jika dikosongkan, kategori akan dideteksi dari tag Google Maps.'),

                    Select::make('city_id')
                        ->label('Tetapkan Kota (Opsional)')
                        ->options(City::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Otomatis deteksi dari Alamat')
                        ->helperText('Jika dikosongkan, kota akan dideteksi dari parsing alamat.'),

                    TextInput::make('target_limit')
                        ->label('Jumlah Target Tempat')
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
                        ->label('Ambil Foto Galeri')
                        ->default(true),

                    Toggle::make('include_reviews')
                        ->label('Ambil Ulasan Pengguna')
                        ->default(false),

                    Toggle::make('publish_imported')
                        ->label('Langsung Publikasikan')
                        ->default(false)
                        ->columnSpanFull()
                        ->helperText('Jika aktif, status langsung Published. Jika tidak, akan Draft.'),
                ])
                ->columns(2)
                ->action(function (array $data) {
                    $job = GoogleMapsScrapeJob::create([
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
