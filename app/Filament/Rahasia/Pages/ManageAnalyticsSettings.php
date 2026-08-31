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

class ManageAnalyticsSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $navigationLabel = 'Analytics & Tracking';

    protected static ?string $title = 'Pengaturan Analytics & Tracking';

    protected static ?string $slug = 'settings/analytics';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ga_enabled' => (bool) Setting::get('ga_enabled', false),
            'ga_measurement_id' => Setting::get('ga_measurement_id', ''),
            'gtm_enabled' => (bool) Setting::get('gtm_enabled', false),
            'gtm_container_id' => Setting::get('gtm_container_id', ''),
            'umami_enabled' => (bool) Setting::get('umami_enabled', false),
            'umami_website_id' => Setting::get('umami_website_id', ''),
            'umami_script_url' => Setting::get('umami_script_url', 'https://cloud.umami.is/script.js'),
            'custom_head_scripts' => Setting::get('custom_head_scripts', ''),
            'custom_body_scripts' => Setting::get('custom_body_scripts', ''),
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
                Section::make('Google Analytics 4 (GA4)')
                    ->description('Lacak kunjungan, perilaku pengguna, dan konversi dengan Google Analytics 4.')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->schema([
                        Toggle::make('ga_enabled')
                            ->label('Aktifkan Google Analytics 4')
                            ->helperText('Jika diaktifkan, tag gtag.js akan dimuat di <head>.'),

                        TextInput::make('ga_measurement_id')
                            ->label('Measurement ID (G-XXXXXXXXXX)')
                            ->placeholder('G-XXXXXXXXXX')
                            ->maxLength(50)
                            ->helperText('Dapatkan dari Google Analytics -> Admin -> Data Streams -> Web.'),
                    ]),

                Section::make('Google Tag Manager (GTM)')
                    ->description('Kelola tag pemasaran dan analytics pihak ketiga secara terpusat.')
                    ->icon(Heroicon::OutlinedTag)
                    ->schema([
                        Toggle::make('gtm_enabled')
                            ->label('Aktifkan Google Tag Manager')
                            ->helperText('Memuat script GTM di <head> dan noscript iframe di awal <body>.'),

                        TextInput::make('gtm_container_id')
                            ->label('GTM Container ID (GTM-XXXXXXX)')
                            ->placeholder('GTM-XXXXXXX')
                            ->maxLength(50)
                            ->helperText('Dapatkan dari dashboard Google Tag Manager.'),
                    ]),

                Section::make('Umami Analytics (Privacy-Friendly)')
                    ->description('Analytics modern yang ramah privasi, ringan, dan tidak membutuhkan cookie banner.')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->schema([
                        Toggle::make('umami_enabled')
                            ->label('Aktifkan Umami Analytics')
                            ->helperText('Memuat script pelacakan Umami secara asynchronous.'),

                        TextInput::make('umami_website_id')
                            ->label('Umami Website ID')
                            ->placeholder('contoh: a1b2c3d4-e5f6-7890-abcd-ef1234567890')
                            ->maxLength(100)
                            ->helperText('Dapatkan dari Dashboard Umami -> Websites -> Edit -> Website ID.'),

                        TextInput::make('umami_script_url')
                            ->label('Umami Script URL')
                            ->placeholder('https://cloud.umami.is/script.js')
                            ->default('https://cloud.umami.is/script.js')
                            ->maxLength(255)
                            ->helperText('Gunakan https://cloud.umami.is/script.js untuk Umami Cloud, atau domain self-hosted Anda.'),
                    ]),

                Section::make('Custom Scripts (Header & Footer)')
                    ->description('Tambahkan tag verifikasi Search Console, Meta Pixel, atau script kustom lainnya.')
                    ->icon(Heroicon::OutlinedCodeBracket)
                    ->schema([
                        Textarea::make('custom_head_scripts')
                            ->label('Custom Script di <head>')
                            ->placeholder("<meta name=\"google-site-verification\" content=\"...\" />\n<!-- atau script analitik kustom -->")
                            ->rows(5)
                            ->helperText('Dimuat sebelum penutup tag </head>. Pastikan menggunakan tag <script> atau <meta> yang valid.'),

                        Textarea::make('custom_body_scripts')
                            ->label('Custom Script di Akhir <body>')
                            ->placeholder("<!-- Custom tracking / live chat widget -->")
                            ->rows(5)
                            ->helperText('Dimuat sebelum penutup tag </body>.'),
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
                                ->label('Simpan Pengaturan')
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
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Konfigurasi analytics dan tracking telah diperbarui.')
            ->success()
            ->send();
    }
}
