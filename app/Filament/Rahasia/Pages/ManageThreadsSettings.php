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

    public function mount(): void
    {
        $this->form->fill([
            'threads_auto_post_enabled' => (bool) Setting::get('threads_auto_post_enabled', true),
            'threads_user_id' => Setting::get('threads_user_id', config('services.threads.user_id', '')),
            'threads_access_token' => Setting::get('threads_access_token', config('services.threads.access_token', '')),
            'threads_content_type' => Setting::get('threads_content_type', 'all'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Kredensial Meta Threads API')
                    ->description('Akun pengembang Meta resmi untuk memublikasikan konten otomatis ke profil Threads @disekitarcom.')
                    ->icon(Heroicon::OutlinedKey)
                    ->schema([
                        Toggle::make('threads_auto_post_enabled')
                            ->label('Aktifkan Auto-Post Terjadwal ke Threads')
                            ->helperText('Jika aktif, scheduler akan memposting 1 artikel/loker baru 3x sehari (08:00, 12:30, 19:00 WIB).'),

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
                                ->modalDescription('Paste isi postingan Thread seseorang (loker, kuliner, wisata, atau opini lokal). AI akan merumuskan 3 opsi balasan cerdas & santai untuk kamu copy-paste ke Threads.')
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
                                        ->rows(4)
                                        ->required(),
                                ])
                                ->modalSubmitActionLabel('Generate 3 Draft Reply')
                                ->action(function (array $data, ThreadsService $service) {
                                    $drafts = $service->generateEngagementDrafts($data['thread_text'], $data['topic']);

                                    $body = '';
                                    foreach ($drafts as $index => $d) {
                                        $num = $index + 1;
                                        $style = $d['style'] ?? 'Opsi';
                                        $text = $d['text'] ?? '';
                                        $body .= "**{$num}. [{$style}]**\n\"{$text}\"\n\n";
                                    }

                                    Notification::make()
                                        ->title('Draft Balasan AI Siap Dicopy:')
                                        ->body($body)
                                        ->persistent()
                                        ->success()
                                        ->send();
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
            Setting::set($key, is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? ''));
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
            $post = \App\Models\Post::query()
                ->where('status', 'published')
                ->latest('published_at')
                ->first();

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
