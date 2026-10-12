<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageAdSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Iklan & AdSense';

    protected static ?string $title = 'Pengaturan Google AdSense & Penempatan Iklan';

    protected static ?string $slug = 'settings/ads';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'adsense_enabled' => (bool) Setting::get('adsense_enabled', false),
            'adsense_client_id' => Setting::get('adsense_client_id', ''),
            'adsense_auto_ads' => (bool) Setting::get('adsense_auto_ads', false),
            'ads_txt_content' => Setting::get('ads_txt_content', "google.com, pub-XXXXXXXXXXXXXXXX, DIRECT, f08c47fec0942fa0"),

            'adsterra_enabled' => (bool) Setting::get('adsterra_enabled', false),
            'adsterra_head_code' => Setting::get('adsterra_head_code', ''),
            'adsterra_popunder_code' => Setting::get('adsterra_popunder_code', ''),
            'adsterra_socialbar_code' => Setting::get('adsterra_socialbar_code', ''),

            // Home display & ads
            'ad_home_hero_enabled' => (bool) Setting::get('ad_home_hero_enabled', false),
            'ad_home_hero_code' => Setting::get('ad_home_hero_code', ''),
            'ad_home_sidebar_enabled' => (bool) Setting::get('ad_home_sidebar_enabled', false),
            'ad_home_sidebar_code' => Setting::get('ad_home_sidebar_code', ''),

            // Post detail ads
            'ad_post_sidebar_top_enabled' => (bool) Setting::get('ad_post_sidebar_top_enabled', false),
            'ad_post_sidebar_top_code' => Setting::get('ad_post_sidebar_top_code', ''),
            'ad_post_before_gallery_enabled' => (bool) Setting::get('ad_post_before_gallery_enabled', false),
            'ad_post_before_gallery_code' => Setting::get('ad_post_before_gallery_code', ''),

            // Category ads
            'ad_category_sidebar_enabled' => (bool) Setting::get('ad_category_sidebar_enabled', false),
            'ad_category_sidebar_code' => Setting::get('ad_category_sidebar_code', ''),

            // Search ads
            'ad_search_top_enabled' => (bool) Setting::get('ad_search_top_enabled', false),
            'ad_search_top_code' => Setting::get('ad_search_top_code', ''),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konfigurasi Akun Google AdSense')
                    ->description('Hubungkan akun Google AdSense untuk otomatis memuat script iklan di seluruh website.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        Toggle::make('adsense_enabled')
                            ->label('Aktifkan Google AdSense')
                            ->helperText('Jika diaktifkan, script Google AdSense akan otomatis dimuat di header.'),

                        TextInput::make('adsense_client_id')
                            ->label('AdSense Publisher ID (ca-pub-XXXXXXXXXXXXXXXX)')
                            ->placeholder('ca-pub-1234567890123456')
                            ->maxLength(60)
                            ->helperText('Dapatkan dari akun Google AdSense Anda.'),

                        Toggle::make('adsense_auto_ads')
                            ->label('Aktifkan Auto Ads (Iklan Otomatis Google)')
                            ->helperText('Google akan otomatis menempatkan iklan di posisi terbaik berdasarkan AI.'),

                        Textarea::make('ads_txt_content')
                            ->label('Isi File ads.txt')
                            ->placeholder('google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0')
                            ->rows(3)
                            ->helperText('Otomatis disajikan di URL https://carikaryawan.com/ads.txt untuk verifikasi kepemilikan oleh Google AdSense.'),
                    ]),

                Section::make('Konfigurasi Jaringan Iklan Adsterra')
                    ->description('Kelola script global Adsterra seperti Social Bar, Popunder, atau script verifikasi.')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->schema([
                        Toggle::make('adsterra_enabled')
                            ->label('Aktifkan Jaringan Adsterra')
                            ->helperText('Master switch untuk mengaktifkan script global Adsterra di website.'),

                        Textarea::make('adsterra_head_code')
                            ->label('Script Header Adsterra (Head Tag / Verifikasi)')
                            ->placeholder("<script type='text/javascript' src='//...js'></script>")
                            ->rows(3)
                            ->helperText('Dimuat di bagian <head> website (opsional, jika Adsterra meminta verifikasi kode/script head).'),

                        Textarea::make('adsterra_socialbar_code')
                            ->label('Kode Adsterra Social Bar / In-Page Push')
                            ->placeholder("<script type='text/javascript' src='//...js'></script>")
                            ->rows(3)
                            ->helperText('Dimuat di bagian bawah <body> website untuk format Social Bar / Push Notifications iklan.'),

                        Textarea::make('adsterra_popunder_code')
                            ->label('Kode Adsterra Popunder (Opsional)')
                            ->placeholder("<script type='text/javascript' src='//...js'></script>")
                            ->rows(3)
                            ->helperText('Dimuat di bagian bawah <body>. Biarkan kosong jika tidak ingin menggunakan popunder demi kenyamanan pembaca.'),
                    ]),

                Section::make('Tata Letak Iklan: Beranda (Home)')
                    ->description('Pengaturan penempatan iklan pada halaman utama.')
                    ->icon(Heroicon::OutlinedHome)
                    ->schema([
                        Toggle::make('ad_home_hero_enabled')
                            ->label('Iklan di Bawah Hero Banner (Sebelum Tab Kategori)')
                            ->helperText('Tampilkan slot iklan horizontal di bawah hero.'),

                        Textarea::make('ad_home_hero_code')
                            ->label('Kode Iklan (Bawah Hero)')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4)
                            ->helperText('Masukkan kode unit iklan AdSense responsif atau kode banner iklan kustom.'),

                        Toggle::make('ad_home_sidebar_enabled')
                            ->label('Iklan Sidebar (Antara Loker Pilihan & Jelajah Kota)')
                            ->helperText('Tampilkan slot iklan di sidebar kanan beranda.'),

                        Textarea::make('ad_home_sidebar_code')
                            ->label('Kode Iklan Sidebar Beranda')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4),
                    ]),

                Section::make('Tata Letak Iklan: Detail Lowongan')
                    ->description('Posisi penempatan iklan pada halaman detail lowongan kerja.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        Toggle::make('ad_post_sidebar_top_enabled')
                            ->label('Iklan Sidebar Atas (Di Atas Card Info Lowongan)')
                            ->helperText('Tampilkan iklan di bagian paling atas sidebar detail post.'),

                        Textarea::make('ad_post_sidebar_top_code')
                            ->label('Kode Iklan (Sidebar Atas Post)')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4),

                        Toggle::make('ad_post_before_gallery_enabled')
                            ->label('Iklan Tengah Konten (Di Atas Bagian Galeri/Logo)')
                            ->helperText('Tampilkan iklan setelah isi deskripsi lowongan, sebelum bagian galeri.'),

                        Textarea::make('ad_post_before_gallery_code')
                            ->label('Kode Iklan (Di Atas Galeri)')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4),
                    ]),

                Section::make('Tata Letak Iklan: Halaman Kategori')
                    ->description('Posisi penempatan iklan pada halaman daftar kategori lowongan per kategori.')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->schema([
                        Toggle::make('ad_category_sidebar_enabled')
                            ->label('Iklan Sidebar Kategori (Antara Loker Pilihan & Jelajah Kota)')
                            ->helperText('Tampilkan slot iklan di sidebar kanan halaman kategori.'),

                        Textarea::make('ad_category_sidebar_code')
                            ->label('Kode Iklan Sidebar Kategori')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4),
                    ]),

                Section::make('Tata Letak Iklan: Halaman Pencarian (Cari)')
                    ->description('Posisi penempatan iklan pada halaman hasil pencarian & filter.')
                    ->icon(Heroicon::OutlinedMagnifyingGlass)
                    ->schema([
                        Toggle::make('ad_search_top_enabled')
                            ->label('Iklan di Bawah Kotak Filter Pencarian')
                            ->helperText('Tampilkan slot iklan horizontal di atas counter hasil pencarian.'),

                        Textarea::make('ad_search_top_code')
                            ->label('Kode Iklan Halaman Cari')
                            ->placeholder("<ins class=\"adsbygoogle\" ...></ins>\n<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>")
                            ->rows(4),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Simpan Pengaturan Iklan')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''));
        }

        Notification::make()
            ->title('Pengaturan Iklan Berhasil Disimpan')
            ->body('Konfigurasi Google AdSense dan tata letak iklan telah diperbarui.')
            ->success()
            ->send();
    }
}
