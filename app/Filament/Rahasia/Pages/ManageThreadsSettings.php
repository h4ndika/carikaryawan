<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\Setting;
use App\Services\ThreadsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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

class ManageThreadsSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationLabel = 'Otomasi Threads';

    protected static ?string $title = 'Pengaturan Otomasi Threads (Meta API)';

    protected static ?string $slug = 'settings/threads';

    protected static ?int $navigationSort = 110;

    public ?array $data = [];

    public array $generatedReplyDrafts = [];

    public function mount(): void
    {
        $savedCities = Setting::get('threads_target_cities');
        $targetCities = !empty($savedCities) ? json_decode($savedCities, true) : ThreadsService::getDefaultCityIds();

        $this->form->fill([
            'threads_auto_post_enabled' => (bool) Setting::get('threads_auto_post_enabled', true),
            'threads_user_id' => Setting::get('threads_user_id', config('services.threads.user_id', '')),
            'threads_access_token' => Setting::get('threads_access_token', config('services.threads.access_token', '')),
            'threads_content_type' => Setting::get('threads_content_type', 'all'),
            'threads_target_cities' => $targetCities,
            'threads_schedule_slot_1_enabled' => (bool) Setting::get('threads_schedule_slot_1_enabled', true),
            'threads_schedule_slot_2_enabled' => (bool) Setting::get('threads_schedule_slot_2_enabled', true),
            'threads_schedule_slot_3_enabled' => (bool) Setting::get('threads_schedule_slot_3_enabled', true),
            'threads_schedule_slot_4_enabled' => (bool) Setting::get('threads_schedule_slot_4_enabled', true),
            'threads_schedule_slot_5_enabled' => (bool) Setting::get('threads_schedule_slot_5_enabled', true),
            'threads_schedule_slot_6_enabled' => (bool) Setting::get('threads_schedule_slot_6_enabled', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Master Sakelar Otomasi')
                    ->description('Sakelar utama untuk menghidupkan atau mematikan seluruh otomasi Threads sekaligus.')
                    ->schema([
                        Toggle::make('threads_auto_post_enabled')
                            ->label('Aktifkan Auto-Post Terjadwal ke Threads (Master On/Off)')
                            ->helperText('Jika dinonaktifkan, semua jadwal posting di bawah otomatis berhenti tanpa mengirim request ke Meta.'),
                    ]),

                Section::make('Jadwal Harian Posting (6 Slot)')
                    ->description('Pilih slot jam posting mana saja yang ingin diaktifkan atau diistirahatkan.')
                    ->icon(Heroicon::OutlinedClock)
                    ->columns(2)
                    ->schema([
                        Toggle::make('threads_schedule_slot_1_enabled')
                            ->label('🌅 Pagi (07:30 WIB)')
                            ->helperText('Konten: Lowongan Kerja Terbaru #1'),

                        Toggle::make('threads_schedule_slot_2_enabled')
                            ->label('☀️ Menjelang Siang (10:30 WIB)')
                            ->helperText('Konten: Lowongan Kerja Terbaru #2'),

                        Toggle::make('threads_schedule_slot_3_enabled')
                            ->label('🍽️ Istirahat Siang (12:30 WIB)')
                            ->helperText('Konten: Rekomendasi Tempat (Kuliner, Kafe, Wisata)'),

                        Toggle::make('threads_schedule_slot_4_enabled')
                            ->label('☕ Sore Hari (15:30 WIB)')
                            ->helperText('Konten: Lowongan Kerja Terbaru #3'),

                        Toggle::make('threads_schedule_slot_5_enabled')
                            ->label('🚌 Pulang Kerja (18:30 WIB)')
                            ->helperText('Konten: Lowongan Kerja Terbaru #4'),

                        Toggle::make('threads_schedule_slot_6_enabled')
                            ->label('🌙 Santai Malam (20:30 WIB)')
                            ->helperText('Konten: Artikel Blog & Panduan Kurasi'),
                    ]),

                Section::make('Kredensial Meta Threads API')
                    ->description('Akun pengembang Meta resmi untuk memublikasikan konten otomatis ke profil Threads @disekitarcom.')
                    ->icon(Heroicon::OutlinedKey)
                    ->schema([
                        TextInput::make('threads_user_id')
                            ->label('Threads User ID')
                            ->required()
                            ->placeholder('28590452627311211')
                            ->helperText('ID numerik pengguna Threads dari Meta Graph API.'),

                        TextInput::make('threads_access_token')
                            ->label('Threads Long-Lived Access Token')
                            ->password()
                            ->revealable()
                            ->required()
                            ->helperText('Token akses Threads yang valid.'),

                        Select::make('threads_content_type')
                            ->label('Kategori Konten yang Diprioritaskan')
                            ->options([
                                'all' => 'Semua Konten (Blog Artikel, Lowongan Kerja & Tempat Rekomendasi)',
                                'articles' => 'Hanya Artikel Blog (Panduan & Listicle)',
                                'jobs' => 'Hanya Lowongan Kerja (Loker)',
                                'places' => 'Hanya Tempat & Rekomendasi (Kafe, Hotel, Resto, Wisata)',
                            ])
                            ->default('all')
                            ->helperText('Pilih jenis konten yang ingin dibagikan secara otomatis ke linimasa Threads.'),

                        Select::make('threads_target_cities')
                            ->label('Kota Target untuk Otomasi Post')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return \App\Models\City::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->helperText('Pilih kota-kota yang ingin diprioritaskan untuk auto-post ke Threads. Kosongkan jika ingin mencakup seluruh kota di Indonesia.')
                            ->placeholder('Pilih satu atau lebih kota (Default: Bandung, Jakarta, Yogyakarta, Bali)...'),
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
                                ->label('Simpan Pengaturan Threads')
                                ->submit('save'),

                            Action::make('suggestReplies')
                                ->label('AI Reply Suggester (Interaksi Thread Luar)')
                                ->color('warning')
                                ->icon(Heroicon::OutlinedSparkles)
                                ->modalHeading('AI Reply Suggester — Niche Engagement')
                                ->modalDescription('Paste postingan/pertanyaan akun Thread orang lain. AI akan menghasilkan 3 opsi balasan siap copas langsung di bawah.')
                                ->modalWidth(\Filament\Support\Enums\Width::TwoExtraLarge)
                                ->form([
                                    Select::make('topic')
                                        ->label('Topik Bahasan')
                                        ->options([
                                            'loker' => 'Lowongan Kerja / Karier / Interview',
                                            'kuliner' => 'Kuliner / Tempat Makan / Kafe',
                                            'wisata' => 'Wisata / Rekomendasi Tempat Lokal',
                                            'umum' => 'Santai / Percakapan Komunitas Umum',
                                        ])
                                        ->default('loker')
                                        ->required(),

                                    Textarea::make('thread_text')
                                        ->label('Isi Postingan / Pertanyaan Thread Orang Lain')
                                        ->placeholder('Contoh: Menurut kalian kenapa sekarang cari kerja admin di kota besar susah banget ya? Persaingannya gila-gilaan...')
                                        ->rows(3)
                                        ->required(),
                                ])
                                ->modalContentFooter(fn () => view('filament.rahasia.components.threads-reply-drafts', [
                                    'drafts' => $this->generatedReplyDrafts,
                                ]))
                                ->modalSubmitActionLabel('⚡ Generate / Buat Ulang Draft')
                                ->modalCancelActionLabel('Tutup')
                                ->action(function (array $data, ThreadsService $service, Action $action) {
                                    $this->generatedReplyDrafts = $service->generateEngagementDrafts($data['thread_text'], $data['topic']);

                                    $count = count($this->generatedReplyDrafts);
                                    Notification::make()
                                        ->title("{$count} Opsi Draft Balasan Berhasil Dibuat!")
                                        ->body('Silakan pilih salah satu opsi di bawah dan klik Salin Teks.')
                                        ->success()
                                        ->send();

                                    $action->halt();
                                }),

                            Action::make('testPost')
                                ->label('Kirim Tes Post ke Threads Sekarang')
                                ->color('gray')
                                ->icon(Heroicon::OutlinedPaperAirplane)
                                ->action('sendTestPost')
                                ->requiresConfirmation()
                                ->modalHeading('Kirim Tes Publikasi ke Threads')
                                ->modalDescription('Aksi ini akan mempublikasikan 1 postingan terbaru ke akun Threads @disekitarcom untuk memverifikasi koneksi API.'),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            if (is_array($value)) {
                Setting::set($key, json_encode(array_values(array_map('intval', $value))));
            } else {
                Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''));
            }
        }

        Notification::make()
            ->title('Pengaturan Threads Disimpan')
            ->body('Konfigurasi kredensial dan preferensi auto-post Threads berhasil diperbarui.')
            ->success()
            ->send();
    }

    public function sendTestPost(ThreadsService $threadsService): void
    {
        $this->save();

        try {
            $targetCityIds = ThreadsService::getTargetCityIds();

            $query = \App\Models\Post::query()->where('status', 'published');
            if (!empty($targetCityIds)) {
                $query->whereIn('city_id', $targetCityIds);
            }

            $post = $query->latest('published_at')->first() 
                ?: \App\Models\Post::query()->where('status', 'published')->latest('published_at')->first();

            if (!$post) {
                Notification::make()
                    ->title('Tidak Ada Postingan')
                    ->body('Belum ada postingan dengan status published untuk di-test.')
                    ->warning()
                    ->send();
                return;
            }

            $res = $threadsService->publishPost($post);

            Notification::make()
                ->title('Berhasil Terbit di Threads!')
                ->body("Postingan '{$post->title}' berhasil dipublikasikan. Thread ID: " . ($res['id'] ?? ''))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Kirim ke Threads')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
