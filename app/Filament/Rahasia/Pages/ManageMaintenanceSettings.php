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

class ManageMaintenanceSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $navigationLabel = 'Status Maintenance';

    protected static ?string $title = 'Pengaturan Pemeliharaan Sistem (Maintenance Mode)';

    protected static ?string $slug = 'settings/maintenance';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'maintenance_mode_enabled' => (bool) Setting::get('maintenance_mode_enabled', false),
            'csr_api_enabled' => (bool) Setting::get('csr_api_enabled', true),
            'maintenance_title' => Setting::get('maintenance_title', 'Sistem Sedang Dalam Pemeliharaan'),
            'maintenance_message' => Setting::get('maintenance_message', 'Kami sedang melakukan peningkatan performa dan pemeliharaan berkala untuk memberikan pengalaman terbaik. Layanan akan segera kembali normal.'),
            'maintenance_estimated_time' => Setting::get('maintenance_estimated_time', 'Segera / Dalam beberapa saat'),
            'maintenance_ip_whitelist' => Setting::get('maintenance_ip_whitelist', ''),
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
                Section::make('Saklar Mode Maintenance')
                    ->description('Saat diaktifkan, halaman publik web (depan, detail, cari, dll) akan menampilkan layout pemeliharaan tanpa redirect URL.')
                    ->icon(Heroicon::OutlinedShieldExclamation)
                    ->schema([
                        Toggle::make('maintenance_mode_enabled')
                            ->label('Aktifkan Mode Maintenance')
                            ->helperText('Jika ON, semua pengunjung halaman publik web akan melihat tampilan pemeliharaan. Seluruh endpoint API (/api/...) dan Admin Panel tetap aktif normal 24/7.'),
                    ]),

                Section::make('Saklar Layanan API CSR (Headless Engine)')
                    ->description('Kontrol on/off untuk API yang dikonsumsi oleh web-web frontend CSR (Vercel, GitHub Pages, dll).')
                    ->icon(Heroicon::OutlinedServer)
                    ->schema([
                        Toggle::make('csr_api_enabled')
                            ->label('Aktifkan Layanan API CSR (/api/csr/...)')
                            ->helperText('Jika OFF, semua request data loker dan sitemap dari web-web CSR eksternal akan dimatikan (HTTP 403 Forbidden).'),
                    ]),

                Section::make('Pesan & Tampilan')
                    ->description('Sesuaikan teks yang tampil di layar pemeliharaan pengunjung.')
                    ->icon(Heroicon::OutlinedChatBubbleBottomCenterText)
                    ->schema([
                        TextInput::make('maintenance_title')
                            ->label('Judul Pemeliharaan')
                            ->placeholder('Sistem Sedang Dalam Pemeliharaan')
                            ->required()
                            ->maxLength(200),

                        Textarea::make('maintenance_message')
                            ->label('Pesan Detail')
                            ->placeholder('Pesan penjelasan pemeliharaan...')
                            ->rows(4)
                            ->required(),

                        TextInput::make('maintenance_estimated_time')
                            ->label('Perkiraan Selesai')
                            ->placeholder('Contoh: Pukul 18:00 WIB / 30 Menit lagi')
                            ->maxLength(100),
                    ]),

                Section::make('Whitelist Akses IP (Opsional)')
                    ->description('Daftar IP yang tetap boleh mengakses halaman publik meski sedang maintenance.')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->schema([
                        TextInput::make('maintenance_ip_whitelist')
                            ->label('IP Whitelist (Pisahkan dengan koma)')
                            ->placeholder('127.0.0.1, 103.197.189.196, ...')
                            ->helperText('IP publik Anda: ' . (request()->ip() ?: 'Unknown')),
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
                                ->label('Simpan Status Maintenance')
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
            ->title('Status Maintenance Berhasil Diperbarui')
            ->body('Perubahan status pemeliharaan sistem telah disimpan.')
            ->success()
            ->send();
    }
}
