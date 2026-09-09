<?php

namespace App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\Pages;

use App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\GoogleMapsScrapeJobResource;
use App\Jobs\RunGoogleMapsScrapeJob;
use App\Models\ApifyAccount;
use App\Models\Category;
use App\Models\City;
use App\Models\Province;
use App\Models\GoogleMapsScrapeJob;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

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
                            'jobstreet' => 'JobStreet Indonesia (shahidirfan/jobstreet-scraper) — Rekomendasi Utama',
                            'glints' => 'Glints Indonesia (truefetch/glints-job-listing) — Lengkap & Terstruktur',
                            'google-maps' => 'Google Maps (compass/crawler-google-places) — Tempat & Fasilitas',
                        ])
                        ->default('jobstreet')
                        ->required()
                        ->live()
                        ->columnSpanFull()
                        ->helperText('Pilih mesin pencari: JobStreet untuk loker hemat ($0.99/1k), Glints untuk data loker terperinci, atau Google Maps untuk tempat.'),

                    TextInput::make('query')
                        ->label(fn (Get $get) => match ($get('engine')) {
                            'jobstreet' => 'Kata Kunci Posisi / Pekerjaan (JobStreet)',
                            'glints' => 'Kata Kunci Posisi / Pekerjaan (Glints)',
                            default => 'Kata Kunci Tempat Google Maps'
                        })
                        ->placeholder(fn (Get $get) => match ($get('engine')) {
                            'jobstreet' => 'Contoh: Barista, Staff Gudang, Admin, Sales',
                            'glints' => 'Contoh: Barista, Staff Gudang, Admin, Cook Helper',
                            default => 'Contoh: Hotel di Bandung, Kafe di Jogja, Penitipan koper dekat Stasiun Bandung'
                        })
                        ->required()
                        ->columnSpanFull()
                        ->helperText(fn (Get $get) => match ($get('engine')) {
                            'jobstreet' => 'Ketik posisi lowongan kerja yang dicari di JobStreet Indonesia.',
                            'glints' => 'Ketik posisi lowongan kerja di Glints Indonesia.',
                            default => 'Ketik kata kunci tempat atau fasilitas transit di sekitar stasiun/bandara.'
                        }),

                    TextInput::make('location')
                        ->label('Lokasi / Kota Spesifik (Opsional)')
                        ->placeholder('Contoh: Bandung, Jakarta, Yogyakarta')
                        ->columnSpanFull()
                        ->helperText('Filter lokasi lowongan atau tempat (opsional).'),

                    Select::make('category_id')
                        ->label('Tetapkan Kategori (Opsional)')
                        ->options(fn () => Category::pluck('name', 'id'))
                        ->searchable()
                        ->default(fn (Get $get) => in_array($get('engine'), ['jobstreet', 'glints']) 
                            ? Category::where('slug', 'lowongan-kerja')->value('id') 
                            : null
                        )
                        ->placeholder('Otomatis deteksi dari sumber')
                        ->helperText('Jika dikosongkan, kategori akan dideteksi otomatis.'),

                    Select::make('city_id')
                        ->label('Tetapkan Kota (Opsional)')
                        ->options(fn () => City::pluck('name', 'id'))
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
                        ->options(fn () => 
                            ApifyAccount::active()->pluck('label', 'id')->map(function ($label, $id) {
                                return $label ?: 'Akun #' . $id;
                            })
                        )
                        ->placeholder('Rotasi Otomatis (Load Balancing)')
                        ->helperText('Pilih akun tertentu atau biarkan otomatis memilih akun yang siap pakai.'),

                    Toggle::make('include_images')
                        ->label('Ambil Foto / Gambar')
                        ->default(true)
                        ->visible(fn (Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('include_reviews')
                        ->label('Ambil Ulasan Pengguna')
                        ->default(false)
                        ->visible(fn (Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('publish_imported')
                        ->label('Langsung Publikasikan')
                        ->default(false)
                        ->helperText('Jika aktif, status langsung Published. Jika tidak, akan Draft.'),
                ])
                ->action(function (array $data) {
                    $job = GoogleMapsScrapeJob::create([
                        'engine' => $data['engine'] ?? 'jobstreet',
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

            Action::make('batchScrape')
                ->label('Ekstraksi Massal (Multi Keyword & Kota)')
                ->icon('heroicon-o-queue-list')
                ->color('success')
                ->modalHeading('Ekstraksi Massal (Multi Keyword & Kota)')
                ->modalDescription('Masukkan beberapa kata kunci dan beberapa kota. Setiap kata kunci akan otomatis dikombinasikan dengan setiap kota (Keyword × Kota) menjadi antrean job terpisah.')
                ->modalSubmitActionLabel('Buat Semua Antrean & Mulai')
                ->form([
                    Select::make('engine')
                        ->label('Pilihan Mesin Scraper')
                        ->options([
                            'jobstreet' => 'JobStreet Indonesia (shahidirfan/jobstreet-scraper) — Rekomendasi Utama',
                            'glints' => 'Glints Indonesia (truefetch/glints-job-listing) — Lengkap & Terstruktur',
                            'google-maps' => 'Google Maps (compass/crawler-google-places) — Tempat & Fasilitas',
                        ])
                        ->default('jobstreet')
                        ->required()
                        ->live()
                        ->columnSpanFull()
                        ->helperText('Pilih mesin pencari untuk seluruh antrean batch ini.'),

                    Textarea::make('queries')
                        ->label('Daftar Kata Kunci (1 baris = 1 kata kunci)')
                        ->placeholder("Barista\nStaff Gudang\nAdmin Kasir\nCook Helper")
                        ->rows(5)
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Ketik kata kunci pencarian, pisahkan dengan baris baru (Enter).'),

                    Select::make('province_ids')
                        ->label('Pilih Provinsi (Otomatis Semua Kota/Kabupaten di Provinsi Ini)')
                        ->options(fn () => Province::pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih satu atau beberapa provinsi (misal: DI Yogyakarta, Bali, Jawa Barat)')
                        ->columnSpanFull()
                        ->helperText('Jika dipilih, seluruh kota/kabupaten di dalam provinsi tersebut akan otomatis diikutsertakan.'),

                    Select::make('city_ids')
                        ->label('Pilih Kota Spesifik / Tambahan (Opsional)')
                        ->options(fn () => City::pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->placeholder('Pilih kota tertentu')
                        ->columnSpanFull()
                        ->helperText('Pilih kota tertentu jika ingin menambah kota di luar provinsi di atas atau hanya kota spesifik.'),

                    Select::make('category_id')
                        ->label('Tetapkan Kategori (Opsional)')
                        ->options(fn () => Category::pluck('name', 'id'))
                        ->searchable()
                        ->default(fn (Get $get) => in_array($get('engine'), ['jobstreet', 'glints']) 
                            ? Category::where('slug', 'lowongan-kerja')->value('id') 
                            : null
                        )
                        ->placeholder('Otomatis deteksi dari sumber')
                        ->helperText('Jika dikosongkan, kategori akan dideteksi otomatis.'),

                    TextInput::make('target_limit')
                        ->label('Jumlah Target Data (per Kombinasi)')
                        ->numeric()
                        ->default(20)
                        ->minValue(1)
                        ->maxValue(500)
                        ->required()
                        ->helperText('Target data yang ditarik untuk masing-masing keyword & kota.'),

                    Select::make('apify_account_id')
                        ->label('Pilih Akun / Token Apify')
                        ->options(fn () => 
                            ApifyAccount::active()->pluck('label', 'id')->map(function ($label, $id) {
                                return $label ?: 'Akun #' . $id;
                            })
                        )
                        ->placeholder('Rotasi Otomatis (Load Balancing)')
                        ->helperText('Biarkan kosong untuk pembagian beban otomatis antar token Apify yang aktif.'),

                    Toggle::make('include_images')
                        ->label('Ambil Foto / Gambar')
                        ->default(true)
                        ->visible(fn (Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('include_reviews')
                        ->label('Ambil Ulasan Pengguna')
                        ->default(false)
                        ->visible(fn (Get $get) => $get('engine') === 'google-maps'),

                    Toggle::make('publish_imported')
                        ->label('Langsung Publikasikan')
                        ->default(false)
                        ->helperText('Jika aktif, status data hasil scrape langsung Published.'),
                ])
                ->action(function (array $data) {
                    $rawQueries = preg_split('/\r\n|\r|\n/', (string) ($data['queries'] ?? ''));
                    $queries = array_values(array_filter(array_map('trim', $rawQueries)));

                    if (empty($queries)) {
                        Notification::make()
                            ->title('Kata kunci tidak boleh kosong!')
                            ->danger()
                            ->send();
                        return;
                    }

                    $cityIds = $data['city_ids'] ?? [];
                    $provinceIds = $data['province_ids'] ?? [];

                    $citiesFromProvinces = !empty($provinceIds)
                        ? City::whereIn('province_id', $provinceIds)->get()
                        : collect();
                    $specificCities = !empty($cityIds)
                        ? City::whereIn('id', $cityIds)->get()
                        : collect();

                    $allCities = $citiesFromProvinces->concat($specificCities)->unique('id');
                    $selectedCities = $allCities->isNotEmpty() ? $allCities : collect([null]);

                    $createdCount = 0;
                    foreach ($queries as $q) {
                        if (empty($q)) continue;

                        foreach ($selectedCities as $city) {
                            $job = GoogleMapsScrapeJob::create([
                                'engine' => $data['engine'] ?? 'jobstreet',
                                'query' => $q,
                                'location' => $city ? $city->name : null,
                                'category_id' => $data['category_id'] ?? null,
                                'city_id' => $city ? $city->id : null,
                                'target_limit' => (int) ($data['target_limit'] ?? 20),
                                'include_images' => (bool) ($data['include_images'] ?? true),
                                'include_reviews' => (bool) ($data['include_reviews'] ?? false),
                                'publish_imported' => (bool) ($data['publish_imported'] ?? false),
                                'apify_account_id' => $data['apify_account_id'] ?? null,
                                'status' => 'pending',
                            ]);

                            RunGoogleMapsScrapeJob::dispatch($job->id);
                            $createdCount++;
                        }
                    }

                    Notification::make()
                        ->title("Ekstraksi Massal Dimulai!")
                        ->body("Berhasil membuat {$createdCount} antrean pekerjaan di background.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
