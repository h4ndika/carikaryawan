<?php

namespace App\Filament\Rahasia\Resources\Articles\Pages;

use App\Filament\Rahasia\Resources\Articles\ArticleResource;
use App\Models\Category;
use App\Models\City;
use App\Services\ArticleCuratorService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListArticles extends ListRecords
{
    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('curateListicle')
                ->label('Kurasi Artikel Otomatis (AI)')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->modalHeading('Kurasi Artikel Listicle Otomatis dari Database')
                ->modalDescription('Sistem akan mengambil tempat dengan rating tertinggi di kota pilihan dan menyusun artikel blog editorial lengkap beserta internal link.')
                ->modalSubmitActionLabel('Mulai Kurasi Sekarang')
                ->form([
                    Select::make('city_id')
                        ->label('Pilih Kota')
                        ->options(
                            City::query()
                                ->whereHas('posts', fn ($q) => $q->published())
                                ->withCount(['posts' => fn ($q) => $q->published()])
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($city) => [$city->id => "{$city->name} ({$city->posts_count} tempat)"])
                        )
                        ->searchable()
                        ->required(),

                    Select::make('category_id')
                        ->label('Topik / Kategori Tempat (Opsional)')
                        ->options(Category::query()->where('slug', 'not like', '%loker%')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Semua Tempat Populer'),

                    Select::make('limit')
                        ->label('Jumlah Rekomendasi Tempat')
                        ->options([
                            3 => '3 Tempat',
                            5 => '5 Tempat (Rekomendasi)',
                            7 => '7 Tempat',
                            10 => '10 Tempat',
                        ])
                        ->default(5)
                        ->required(),

                    TextInput::make('custom_angle')
                        ->label('Sudut Pandang / Angle Khusus (Opsional)')
                        ->placeholder('misal: Cocok untuk liburan keluarga, ramah kantong, atau spot foto instagramable'),
                ])
                ->action(function (array $data) {
                    $curationJob = \App\Models\ArticleCurationJob::create([
                        'city_id' => (int) $data['city_id'],
                        'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
                        'limit' => (int) ($data['limit'] ?? 5),
                        'custom_angle' => $data['custom_angle'] ?? null,
                        'status' => 'pending',
                    ]);

                    \App\Jobs\CurateArticleJob::dispatch(
                        cityId: (int) $data['city_id'],
                        categoryId: !empty($data['category_id']) ? (int) $data['category_id'] : null,
                        limit: (int) ($data['limit'] ?? 5),
                        customAngle: $data['custom_angle'] ?? null,
                        curationJobId: $curationJob->id
                    );

                    Notification::make()
                        ->title('Permintaan Kurasi Dikirim ke Antrean')
                        ->body('Proses pembuatan artikel telah dicatat. Anda dapat memantau perkembangannya di menu "Antrean Artikel".')
                        ->success()
                        ->send();
                }),

            Action::make('triggerAutoHarvest')
                ->label('Jalankan Auto-Harvest Sekarang')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Auto-Harvest Kurasi')
                ->modalDescription('Sistem akan memilih 1 kota dan topik terbaik secara otomatis dari rotasi database, lalu memasukkannya ke antrean pembuatan artikel AI.')
                ->action(function () {
                    \Illuminate\Support\Facades\Artisan::call('articles:auto-curate');
                    Notification::make()
                        ->title('Auto-Harvest Berhasil Dijalankan')
                        ->body('Kurasi otomatis telah dikirim ke antrean latar belakang.')
                        ->success()
                        ->send();
                }),

            Action::make('toggleHomePriority')
                ->label(fn () => (bool) \App\Models\Setting::get('home_prioritize_articles', false)
                    ? 'Utamakan Artikel di Home: AKTIF'
                    : 'Utamakan Artikel di Home: NONAKTIF')
                ->icon(fn () => (bool) \App\Models\Setting::get('home_prioritize_articles', false)
                    ? 'heroicon-s-check-circle'
                    : 'heroicon-o-home')
                ->color(fn () => (bool) \App\Models\Setting::get('home_prioritize_articles', false)
                    ? 'success'
                    : 'gray')
                ->tooltip('Klik untuk mengubah apakah artikel blog editorial diposisikan utama di Beranda (Home)')
                ->action(function () {
                    $current = (bool) \App\Models\Setting::get('home_prioritize_articles', false);
                    $new = ! $current;
                    \App\Models\Setting::set('home_prioritize_articles', $new ? '1' : '0');

                    Notification::make()
                        ->title($new ? 'Artikel Blog Diutamakan di Home' : 'Mode Default Home Diaktifkan')
                        ->body($new 
                            ? 'Halaman Beranda sekarang mengutamakan tampilan artikel editorial di posisi teratas.' 
                            : 'Halaman Beranda kembali ke susunan direktori tempat rekomendasi.')
                        ->color($new ? 'success' : 'info')
                        ->send();
                }),

            CreateAction::make()
                ->label('Tulis Manual'),
        ];
    }
}
