<?php

namespace App\Filament\Rahasia\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\City;
use App\Models\Province;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Masukkan data utama untuk post ini.')
                    ->schema([
                        TextInput::make('title')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug'),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('city_id')
                            ->label('City')
                            ->options(City::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $city = City::find($state);
                                    if ($city && $city->province_id) {
                                        $set('province_id', $city->province_id);
                                    }
                                }
                            }),
                        Select::make('province_id')
                            ->label('Province')
                            ->options(Province::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        RichEditor::make('ai_prompt')
                            ->label('AI Prompt')
                            ->default('Deskripsikan dengan format Tentang, Fasilitas, Lokasi, Harga')
                            ->hintAction(
                                Action::make('generateContent')
                                    ->label('Generate AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('primary')
                                    ->action(function (Get $get, Set $set) {
                                        $judul = $get('title');
                                        $cityName = optional(City::find($get('city_id')))->name;
                                        $provinceName = optional(Province::find($get('province_id')))->name;
                                        $category = Category::find($get('category_id'))?->name ?? '';
                                        $promptUser = $get('ai_prompt');

                                        $finalPrompt = "Buatkan artikel dengan Judul : \"$judul\", yang berlokasi di $cityName, $provinceName. " .
                                            "Artikel ini kategorinya $category. " .
                                            'Tulis dengan bahasa Indonesia yang natural dan menarik seperti majalah editorial. ' .
                                            'PENTING: Langsung mulai dengan judul artikel menggunakan tag <h2> atau <h1>. JANGAN berikan teks pengantar, penjelasan instruksi, pemikiran/reasoning, atau salam pembuka/penutup. ' .
                                            'Format artikel dalam HTML bersih (gunakan tag <h2>, <h3>, <p>, <ul>, <li>), jangan gunakan blok markdown atau backticks. ' .
                                            'Tambahkan juga data detail dalam JSON di akhir output, dibungkus tag <json>...</json> sesuai kategori, misal:' .
                                            '- Kalau kategori seperti Hotel, Tempat Makan dll : harga, lokasi, jam_buka, fasilitas' .
                                            '- Kalau kategori Lowongan Kerja: posisi, gaji, lokasi, kualifikasi, deskripsi_pekerjaan' .
                                            '- dan selanjutnya tolong pikirkan key nya berdasarkan kategori lainnya' .
                                            'Dengan Format Universal seperti ini :' .
                                            '{
                                                "key1": "value1",
                                                "key2": "value2",
                                                "key3": "value3",
                                                "key4": "value4"
                                            }' .
                                            "Prompt tambahan: $promptUser";

                                        try {
                                            $response = OpenAI::chat()->create([
                                                'model' => config('openai.model'),
                                                'messages' => [
                                                    ['role' => 'system', 'content' => 'Kamu adalah penulis artikel editorial profesional. JAWAB HANYA dengan konten HTML artikel langsung (dimulai dengan <h2>/<h1> judul) tanpa pengantar, tanpa pemikiran/reasoning, dan tanpa penutup/salam.'],
                                                    ['role' => 'user', 'content' => $finalPrompt],
                                                ],
                                                'temperature' => 0.7,
                                                'stream' => false,
                                            ]);
                                        } catch (\Throwable $e) {
                                            Notification::make()
                                                ->title('Generate AI gagal')
                                                ->body($e->getMessage())
                                                ->danger()
                                                ->send();

                                            return;
                                        }

                                        $rawGenerated = $response->choices[0]->message->content ?? '';
                                        $cleaned = \App\Services\PostAiContentService::cleanGeneratedContent($rawGenerated);

                                        $set('content', $cleaned['content']);
                                        $set('details', $cleaned['details']);
                                    })
                            ),

                        RichEditor::make('content')
                            ->label('Konten')
                            ->columnSpanFull()
                            ->required(),
                        Repeater::make('details')
                            ->label('Detail Tambahan')
                            ->schema([
                                TextInput::make('meta_key')->label('Key')->required(),
                                Textarea::make('meta_value')->label('Value')->rows(2),
                            ])
                            ->columns(2)
                            ->grid(2)
                            ->minItems(0)
                            ->columnSpanFull()
                            ->dehydrated(),
                        Textarea::make('excerpt')
                            ->label('Excerpt'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('SEO')
                    ->description('Metadata mesin pencari & social share. Kosongkan = fallback title / excerpt / thumbnail. Toggle = salin sekali dari field sumber.')
                    ->schema([
                        Toggle::make('copy_meta_title')
                            ->label('Samakan dengan Title')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state) {
                                    $set('meta_title', $get('title'));
                                }
                            }),
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(70)
                            ->helperText('Target 50–60 karakter. Fallback: title.'),

                        Toggle::make('copy_meta_description')
                            ->label('Samakan dengan Excerpt')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state) {
                                    $set('meta_description', $get('excerpt'));
                                }
                            }),
                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->rows(3)
                            ->maxLength(180)
                            ->helperText('Target 120–160 karakter. Fallback: excerpt.'),

                        TextInput::make('canonical_url')
                            ->label('Canonical URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Kosongkan bila canonical = URL halaman sendiri.'),

                        Select::make('robots')
                            ->label('Robots')
                            ->options([
                                'index,follow' => 'index,follow',
                                'noindex,follow' => 'noindex,follow',
                                'index,nofollow' => 'index,nofollow',
                                'noindex,nofollow' => 'noindex,nofollow',
                            ])
                            ->default('index,follow')
                            ->native(false),

                        Toggle::make('copy_og_image')
                            ->label('Samakan dengan Thumbnail')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if ($state) {
                                    $set('og_image', $get('thumbnail'));
                                }
                            }),
                        TextInput::make('og_image')
                            ->label('OG Image URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Gambar share WA/FB/Twitter. Fallback: thumbnail.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),

                Section::make('Gambar')
                    ->description('URL eksternal disarankan (hemat disk). Upload opsional ke storage lokal. Isi salah satu: URL atau file.')
                    ->schema([
                        TextInput::make('thumbnail')
                            ->label('Thumbnail URL')
                            ->maxLength(255)
                            ->helperText('Cover post. Bisa URL eksternal atau path hasil upload di bawah.')
                            ->columnSpanFull(),
                        FileUpload::make('thumbnail_upload')
                            ->label('Atau upload thumbnail')
                            ->image()
                            ->disk('public')
                            ->directory('posts/thumbnails')
                            ->visibility('public')
                            ->imagePreviewHeight('120')
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (blank($state)) {
                                    return;
                                }
                                // FileUpload single: state = path string; multi: array
                                $path = is_array($state) ? ($state[0] ?? null) : $state;
                                if ($path) {
                                    $set('thumbnail', '/storage/' . ltrim($path, '/'));
                                }
                            })
                            ->helperText('Setelah upload, path diisi ke Thumbnail URL di atas.')
                            ->columnSpanFull(),

                        Repeater::make('images')
                            ->label('Gallery')
                            ->schema([
                                TextInput::make('image_url')
                                    ->label('Image URL')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL eksternal atau path /storage/...'),
                                FileUpload::make('image_upload')
                                    ->label('Atau upload')
                                    ->image()
                                    ->disk('public')
                                    ->directory('posts/gallery')
                                    ->visibility('public')
                                    ->imagePreviewHeight('100')
                                    ->dehydrated(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if (blank($state)) {
                                            return;
                                        }
                                        $path = is_array($state) ? ($state[0] ?? null) : $state;
                                        if ($path) {
                                            $set('image_url', '/storage/' . ltrim($path, '/'));
                                        }
                                    }),
                                TextInput::make('caption')
                                    ->label('Caption')
                                    ->maxLength(255),
                                TextInput::make('order')
                                    ->label('Order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->columnSpanFull()
                            ->dehydrated(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),

                Section::make('Link Afiliasi & Booking')
                    ->description('Tombol booking/reservasi online (Traveloka, Tiket.com, Agoda, Booking.com, dll.) untuk konversi affiliate.')
                    ->schema([
                        Repeater::make('affiliate_links')
                            ->label('Daftar Link Afiliasi')
                            ->schema([
                                Select::make('provider')
                                    ->label('Penyedia / Platform')
                                    ->options([
                                        'traveloka' => 'Traveloka',
                                        'tiket_com' => 'Tiket.com',
                                        'agoda' => 'Agoda',
                                        'booking_com' => 'Booking.com',
                                        'trip_com' => 'Trip.com',
                                        'airbnb' => 'Airbnb',
                                        'pegipegi' => 'Pegipegi',
                                        'custom' => 'Lainnya / Custom',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (blank($get('label'))) {
                                            $labels = [
                                                'traveloka' => 'Pesan di Traveloka',
                                                'tiket_com' => 'Booking di Tiket.com',
                                                'agoda' => 'Cek Kamar di Agoda',
                                                'booking_com' => 'Reservasi di Booking.com',
                                                'trip_com' => 'Pesan di Trip.com',
                                                'airbnb' => 'Lihat di Airbnb',
                                                'pegipegi' => 'Pesan di Pegipegi',
                                                'custom' => 'Pesan Sekarang',
                                            ];
                                            if (isset($labels[$state])) {
                                                $set('label', $labels[$state]);
                                            }
                                        }
                                    }),
                                TextInput::make('label')
                                    ->label('Label Tombol')
                                    ->placeholder('Contoh: Pesan di Traveloka')
                                    ->required(),
                                TextInput::make('url')
                                    ->label('URL Link Afiliasi')
                                    ->url()
                                    ->required()
                                    ->columnSpanFull()
                                    ->placeholder('https://...'),
                                TextInput::make('badge_text')
                                    ->label('Badge Promo / Info Harga (Opsional)')
                                    ->placeholder('Contoh: Diskon 20%, Best Deal, dsb.'),
                                Toggle::make('is_active')
                                    ->label('Aktifkan Tombol')
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->reorderable()
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Data Google Maps')
                    ->description('Informasi terintegrasi dari Google Maps.')
                    ->schema([
                        TextInput::make('place_id')
                            ->label('Place ID')
                            ->maxLength(255)
                            ->disabled(),
                        TextInput::make('rating')
                            ->label('Rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5),
                        TextInput::make('reviews_count')
                            ->label('Jumlah Ulasan')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('price_level')
                            ->label('Tingkat Harga')
                            ->maxLength(50),
                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(50),
                        TextInput::make('website')
                            ->label('Website Resmi')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('google_maps_url')
                            ->label('Google Maps URL')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}