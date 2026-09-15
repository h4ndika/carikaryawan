<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageJobAffiliateSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Afiliasi & Toolkit Karir';

    protected static ?string $title = 'Pengaturan Afiliasi Loker & Toolkit Karir';

    protected static ?string $slug = 'settings/job-affiliate';

    protected static ?int $navigationSort = 105;

    public ?array $data = [];

    public function mount(): void
    {
        $savedItems = Setting::get('job_affiliate_items');
        $items = [];
        if (!empty($savedItems)) {
            $decoded = json_decode($savedItems, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        // Default item template jika belum pernah diisi
        if (empty($items)) {
            $items = [
                [
                    'title' => 'Template CV ATS & Portofolio Siap Pakai',
                    'description' => 'Paket format CV standar HRD & lolos screening ATS, tinggal edit nama & pengalaman.',
                    'platform' => 'shopee',
                    'url' => '',
                    'badge' => 'Terlaris',
                    'is_active' => true,
                ],
                [
                    'title' => 'E-Course Microsoft Excel untuk Admin & Kantor',
                    'description' => 'Kuasai VLOOKUP, Pivot Table, dan formula wajib administrasi kerja dari dasar sampai mahir.',
                    'platform' => 'skill_academy',
                    'url' => '',
                    'badge' => 'Rekomendasi',
                    'is_active' => true,
                ],
            ];
        }

        $this->form->fill([
            'job_affiliate_enabled' => (bool) Setting::get('job_affiliate_enabled', true),
            'job_affiliate_title' => Setting::get('job_affiliate_title', 'Persiapan Lamar Kerja (Toolkit Karir)'),
            'job_affiliate_subtitle' => Setting::get('job_affiliate_subtitle', 'Tingkatkan peluang lolos seleksi dengan referensi panduan, template CV, dan kursus praktis pilihan berikut:'),
            'job_affiliate_items' => $items,
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
                Section::make('Status Fitur Toolkit Karir Loker')
                    ->description('Tampilkan section rekomendasi perlengkapan karir / kursus / template CV otomatis di bawah setiap postingan lowongan kerja.')
                    ->icon(Heroicon::OutlinedSparkles)
                    ->schema([
                        Toggle::make('job_affiliate_enabled')
                            ->label('Aktifkan Toolkit Karir di Seluruh Postingan Loker')
                            ->helperText('Jika diaktifkan, widget rekomendasi toolkit karir akan muncul di bagian bawah artikel lowongan kerja.'),

                        TextInput::make('job_affiliate_title')
                            ->label('Judul Header Section')
                            ->placeholder('Persiapan Lamar Kerja (Toolkit Karir)')
                            ->required(),

                        TextInput::make('job_affiliate_subtitle')
                            ->label('Deskripsi / Subtitle Section')
                            ->placeholder('Tingkatkan peluang lolos seleksi...')
                            ->required(),
                    ]),

                Section::make('Daftar Produk / Kursus Afiliasi')
                    ->description('Kelola tautan afiliasi (Shopee, Tokopedia, Skill Academy, Dicoding, TikTok Shop, dll.) yang relevan untuk para pencari kerja.')
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->schema([
                        Repeater::make('job_affiliate_items')
                            ->label('Daftar Rekomendasi Toolkit Karir')
                            ->schema([
                                Select::make('platform')
                                    ->label('Platform / Merchant')
                                    ->options([
                                        'shopee' => 'Shopee',
                                        'tokopedia' => 'Tokopedia',
                                        'skill_academy' => 'Skill Academy',
                                        'dicoding' => 'Dicoding',
                                        'tiktok' => 'TikTok Shop',
                                        'gramedia' => 'Gramedia',
                                        'custom' => 'Lainnya / Website Sendiri',
                                    ])
                                    ->required()
                                    ->default('shopee'),

                                TextInput::make('badge')
                                    ->label('Badge / Label Khusus')
                                    ->placeholder('Contoh: Terlaris, Diskon 50%, Populer, dsb.')
                                    ->maxLength(50),

                                TextInput::make('title')
                                    ->label('Judul Produk / Materi')
                                    ->placeholder('Contoh: Template CV ATS Lolos Seleksi HRD')
                                    ->required()
                                    ->columnSpanFull(),

                                TextInput::make('description')
                                    ->label('Deskripsi Singkat')
                                    ->placeholder('Contoh: Format Word siap pakai, bonus panduan wawancara kerja...')
                                    ->columnSpanFull(),

                                TextInput::make('url')
                                    ->label('URL Tautan Afiliasi')
                                    ->placeholder('https://shope.ee/... atau tautan afiliasi kamu')
                                    ->url()
                                    ->required()
                                    ->columnSpan(3),

                                Toggle::make('is_active')
                                    ->label('Aktifkan Item Ini')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Item Baru')
                            ->reorderable()
                            ->addActionLabel('+ Tambah Rekomendasi Toolkit')
                            ->columnSpanFull(),
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
                                ->label('Simpan Pengaturan Toolkit Karir')
                                ->submit('save'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            if ($key === 'job_affiliate_items') {
                Setting::set($key, json_encode(array_values($value ?? [])));
            } else {
                Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''));
            }
        }

        Notification::make()
            ->title('Pengaturan Berhasil Disimpan')
            ->body('Konfigurasi dan daftar tautan afiliasi toolkit karir telah diperbarui.')
            ->success()
            ->send();
    }
}
