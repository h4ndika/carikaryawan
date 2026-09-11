<?php

namespace App\Filament\Rahasia\Resources\Posts\Tables;

use App\Jobs\GeneratePostContentJob;
use App\Jobs\ProcessBatchAiQueueJob;
use App\Models\AiGenerationJob;
use App\Models\PhotoScrapeJob;
use App\Models\Post;
use App\Services\PhotoScrapeQueue;
use App\Services\PhotoScraperService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                
                IconColumn::make('content_status')
                    ->label('Konten')
                    ->state(function (Post $record): bool {
                        $words = str_word_count(strip_tags($record->content ?? ''));
                        return $words >= 200;
                    })
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function (Post $record): string {
                        $words = str_word_count(strip_tags($record->content ?? ''));
                        return $words >= 200
                            ? "Artikel lengkap ({$words} kata)"
                            : ($words > 0 ? "Belum lengkap / deskripsi import ({$words} kata, < 200 kata)" : 'Konten masih kosong');
                    }),

                TextColumn::make('images_count')
                    ->label('Foto')
                    ->counts('images')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 3 => 'success',
                        $state >= 1 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => "{$state} Foto")
                    ->tooltip('Klik untuk melihat galeri foto')
                    ->action(
                        Action::make('previewPhotos')
                            ->modalHeading(fn (Post $record) => "Galeri Foto: {$record->title}")
                            ->modalWidth('4xl')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Tutup')
                            ->modalContent(fn (Post $record) => view('filament.components.post-photos-preview-modal', [
                                'post' => $record,
                                'images' => $record->images()->orderBy('order')->get(),
                            ]))
                    ),

                TextColumn::make('excerpt')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label('City')
                    ->searchable(),

                TextColumn::make('province.name')
                    ->label('Province')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

                Filter::make('photos_status')
                    ->form([
                        Select::make('photo_condition')
                            ->label('Status Foto')
                            ->options([
                                'lacking' => 'Kurang / Tanpa Foto (<= 1 Foto)',
                                'complete' => 'Foto Lengkap (>= 2 Foto)',
                                'empty' => 'Tanpa Foto Sama Sekali (0 Foto)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ($data['photo_condition'] ?? null) === 'lacking',
                                fn (Builder $q) => $q->has('images', '<=', 1)
                            )
                            ->when(
                                ($data['photo_condition'] ?? null) === 'complete',
                                fn (Builder $q) => $q->has('images', '>=', 2)
                            )
                            ->when(
                                ($data['photo_condition'] ?? null) === 'empty',
                                fn (Builder $q) => $q->doesntHave('images')
                            );
                    }),

                Filter::make('content_status')
                    ->form([
                        Select::make('has_content')
                            ->label('Ketersediaan Konten')
                            ->options([
                                'filled' => 'Artikel Lengkap (>= 200 kata)',
                                'empty' => 'Belum Lengkap / Import (< 200 kata)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['has_content'] === 'filled',
                                fn (Builder $q) => $q->whereNotNull('content')
                                    ->where('content', '!=', '')
                                    ->whereRaw("(LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1) >= 200")
                            )
                            ->when(
                                $data['has_content'] === 'empty',
                                fn (Builder $q) => $q->where(function ($sub) {
                                    $sub->whereNull('content')
                                        ->orWhere('content', '')
                                        ->orWhereRaw("(LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1) < 200");
                                 })
                            );
                    }),

                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->label('Category'),

                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->label('City')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('province_id')
                    ->relationship('province', 'name')
                    ->label('Province')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dibuat Dari Tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $q, $date): Builder => $q->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('scrapePhoto')
                    ->label('Scrape Foto')
                    ->icon('heroicon-o-camera')
                    ->color('info')
                    ->modalHeading(fn (Post $record) => "Scrape Foto: {$record->title}")
                    ->modalDescription('Cari dan simpan foto tempat secara otomatis dari berbagai sumber.')
                    ->modalSubmitActionLabel('Mulai Scrape Foto')
                    ->form([
                        Select::make('limit')
                            ->label('Jumlah Target Foto')
                            ->options([
                                '4' => '4 Foto',
                                '6' => '6 Foto',
                                '8' => '8 Foto (Standar)',
                                '12' => '12 Foto',
                            ])
                            ->default('8')
                            ->required(),
                        Toggle::make('replace_existing')
                            ->label('Hapus & Gantikan Foto Sebelumnya')
                            ->default(false)
                            ->helperText('Jika nonaktif, foto baru akan ditambahkan ke galeri yang sudah ada.'),
                        Toggle::make('publish_on_complete')
                            ->label('Ubah Status ke Published jika Berhasil')
                            ->default(false),
                    ])
                    ->action(function (Post $record, array $data) {
                        $isAlreadyQueued = PhotoScrapeJob::where('post_id', $record->id)
                            ->whereIn('status', ['pending', 'processing'])
                            ->exists();

                        if ($isAlreadyQueued) {
                            Notification::make()
                                ->title('Post Sudah di Antrean Foto')
                                ->body("Post '{$record->title}' sudah ada di antrean scrape foto.")
                                ->warning()
                                ->actions([
                                    Action::make('lihatAntrean')
                                        ->label('Lihat Antrean')
                                        ->url(route('filament.rahasia.resources.photo-scrape-jobs.index')),
                                ])
                                ->send();
                            return;
                        }

                        $job = PhotoScrapeJob::create([
                            'post_id' => $record->id,
                            'post_title' => $record->title,
                            'status' => 'pending',
                            'limit' => (int) ($data['limit'] ?? 8),
                            'replace_existing' => (bool) ($data['replace_existing'] ?? false),
                            'publish_on_complete' => (bool) ($data['publish_on_complete'] ?? false),
                        ]);

                        $queuedCount = app(PhotoScrapeQueue::class)->enqueue([$job->id]);

                        Notification::make()
                            ->title($queuedCount ? 'Antrean Scrape Foto Dibuat' : 'Gagal Mengirim Antrean; Periksa Detail Job')
                            ->body($queuedCount ? "Scrape foto untuk '{$record->title}' telah dimasukkan ke antrean worker." : "Antrean dibuat, tetapi queue backend gagal. Lihat error di menu Antrean Scrape Foto.")
                            ->actions([
                                Action::make('lihatAntrean')
                                    ->label('Lihat Antrean')
                                    ->url(route('filament.rahasia.resources.photo-scrape-jobs.index')),
                            ])
                            ->success()
                            ->send();
                    }),

                Action::make('generateAi')
                    ->label('AI Content')
                    ->icon('heroicon-o-sparkles')
                    ->color('amber')
                    ->modalHeading(fn (Post $record) => "Generate Konten AI: {$record->title}")
                    ->modalDescription('Proses ini akan menghasilkan artikel HTML, excerpt, dan detail tambahan di latar belakang (Queue).')
                    ->modalSubmitActionLabel('Jalankan Generate AI')
                    ->form([
                        Textarea::make('prompt_additional')
                            ->label('Prompt Tambahan (Opsional)')
                            ->placeholder('Contoh: Fokuskan pada suasana kafe estetik, menu kopi andalan, dan area outdoor.')
                            ->rows(3),
                        Toggle::make('publish_on_complete')
                            ->label('Otomatis ubah status ke Published setelah selesai')
                            ->default(false),
                    ])
                    ->action(function (Post $record, array $data) {
                        $isAlreadyQueued = AiGenerationJob::where('post_id', $record->id)
                            ->whereIn('status', ['pending', 'processing'])
                            ->exists();

                        if ($isAlreadyQueued) {
                            Notification::make()
                                ->title('Post Sudah Berada di Antrean')
                                ->body("Post '{$record->title}' saat ini sedang dalam proses antrean AI. Silakan pantau di menu Antrean AI.")
                                ->warning()
                                ->actions([
                                    Action::make('lihatAntrean')
                                        ->label('Lihat Antrean')
                                        ->url(route('filament.rahasia.resources.ai-generation-jobs.index')),
                                ])
                                ->send();
                            return;
                        }

                        $aiJob = AiGenerationJob::create([
                            'post_id' => $record->id,
                            'post_title' => $record->title,
                            'status' => 'pending',
                            'prompt_additional' => $data['prompt_additional'] ?? null,
                            'publish_on_complete' => (bool) ($data['publish_on_complete'] ?? false),
                        ]);

                        GeneratePostContentJob::dispatch($aiJob->id);

                        Notification::make()
                            ->title('Antrean AI Dibuat')
                            ->body("Generate konten untuk '{$record->title}' telah dimasukkan ke antrean latar belakang.")
                            ->actions([
                                Action::make('lihatAntrean')
                                    ->label('Lihat Antrean')
                                    ->url(route('filament.rahasia.resources.ai-generation-jobs.index')),
                            ])
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('scrapePhotosBulk')
                        ->label('Scrape Foto Terpilih (Batch)')
                        ->icon('heroicon-o-camera')
                        ->color('info')
                        ->accessSelectedRecords()
                        ->modalHeading('Scrape Foto untuk Post Terpilih')
                        ->modalDescription('Cari dan simpan foto tempat secara otomatis di latar belakang untuk post yang dipilih.')
                        ->modalSubmitActionLabel('Mulai Antrean Scrape Foto')
                        ->form([
                            Toggle::make('skip_jobs')
                                ->label('Lewati Kategori Lowongan Kerja')
                                ->default(true)
                                ->helperText('Lowongan kerja tidak memiliki foto tempat Google Maps. Centang untuk melewati kategori ini secara otomatis.'),
                            Toggle::make('only_lacking')
                                ->label('Hanya proses post yang fotonya masih kurang (<= 1 foto)')
                                ->default(true)
                                ->helperText('Jika aktif, post yang sudah memiliki minimal 2 foto tidak akan diproses ulang.'),
                            Select::make('limit')
                                ->label('Jumlah Target Foto per Post')
                                ->options([
                                    '4' => '4 Foto',
                                    '6' => '6 Foto',
                                    '8' => '8 Foto (Standar)',
                                    '12' => '12 Foto',
                                ])
                                ->default('8')
                                ->required(),
                            Toggle::make('replace_existing')
                                ->label('Hapus & Gantikan Foto Sebelumnya')
                                ->default(false),
                            Toggle::make('publish_on_complete')
                                ->label('Otomatis ubah status ke Published jika foto berhasil didapat')
                                ->default(false),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $skipJobs = (bool) ($data['skip_jobs'] ?? true);
                            $onlyLacking = (bool) ($data['only_lacking'] ?? true);
                            $limit = (int) ($data['limit'] ?? 8);
                            $replaceExisting = (bool) ($data['replace_existing'] ?? false);
                            $publishOnComplete = (bool) ($data['publish_on_complete'] ?? false);

                            $dispatchedCount = 0;
                            $skippedCount = 0;
                            $skippedJobCount = 0;
                            $alreadyInQueueCount = 0;
                            $createdJobIds = [];

                            foreach ($records as $post) {
                                if ($skipJobs && $post->isJob()) {
                                    $skippedJobCount++;
                                    continue;
                                }

                                $isAlreadyQueued = PhotoScrapeJob::where('post_id', $post->id)
                                    ->whereIn('status', ['pending', 'processing'])
                                    ->exists();

                                if ($isAlreadyQueued) {
                                    $alreadyInQueueCount++;
                                    continue;
                                }

                                if ($onlyLacking && (($post->isJob() && !empty($post->thumbnail)) || $post->images()->count() >= 2)) {
                                    $skippedCount++;
                                    continue;
                                }

                                $photoJob = PhotoScrapeJob::create([
                                    'post_id' => $post->id,
                                    'post_title' => $post->title,
                                    'status' => 'pending',
                                    'limit' => $limit,
                                    'replace_existing' => $replaceExisting,
                                    'publish_on_complete' => $publishOnComplete,
                                ]);

                                $createdJobIds[] = $photoJob->id;
                                $dispatchedCount++;
                            }

                            $queuedCount = app(PhotoScrapeQueue::class)->enqueue($createdJobIds);
                            $dispatchFailedCount = count($createdJobIds) - $queuedCount;

                            $notes = [];
                            if ($dispatchFailedCount > 0) {
                                $notes[] = "{$dispatchFailedCount} gagal dikirim; periksa error antrean";
                            }
                            $dispatchedCount = $queuedCount;
                            if ($skippedJobCount > 0) {
                                $notes[] = "{$skippedJobCount} lowongan kerja dilewati";
                            }
                            if ($alreadyInQueueCount > 0) {
                                $notes[] = "{$alreadyInQueueCount} dilewati karena sedang dalam antrean";
                            }
                            if ($skippedCount > 0) {
                                $notes[] = "{$skippedCount} dilewati karena foto sudah lengkap (>= 2 foto)";
                            }

                            $noteText = !empty($notes) ? ' (' . implode(', ', $notes) . ')' : '';

                            Notification::make()
                                ->title('Antrean Scrape Foto Dimulai')
                                ->body("{$dispatchedCount} post berhasil dimasukkan ke antrean.{$noteText}")
                                ->actions([
                                    Action::make('lihatAntrean')
                                        ->label('Buka Pemantau Antrean Foto')
                                        ->url(route('filament.rahasia.resources.photo-scrape-jobs.index')),
                                ])
                                ->success()
                                ->send();
                        }),

                    Action::make('generateAiBulk')
                        ->label('Generate Konten Pakai AI')
                        ->icon('heroicon-o-sparkles')
                        ->color('amber')
                        ->accessSelectedRecords()
                        ->modalHeading('Generate Konten AI untuk Post Terpilih')
                        ->modalDescription('Membuat konten, excerpt, dan detail tempat secara otomatis untuk post yang dipilih menggunakan background job queue.')
                        ->modalSubmitActionLabel('Mulai Antrean AI')
                        ->form([
                            Toggle::make('only_empty')
                                ->label('Hanya proses post yang belum memiliki artikel lengkap (< 200 kata)')
                                ->default(true)
                                ->helperText('Jika aktif, post yang sudah memiliki artikel lengkap (>= 200 kata) tidak akan ditimpa.'),
                            Textarea::make('prompt_additional')
                                ->label('Prompt Tambahan (Opsional)')
                                ->placeholder('Contoh: Deskripsikan dengan format Tentang, Fasilitas, Lokasi, Harga.')
                                ->rows(3),
                            Toggle::make('publish_on_complete')
                                ->label('Otomatis ubah status ke Published setelah selesai')
                                ->default(false),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $onlyEmpty = (bool) ($data['only_empty'] ?? true);
                            $promptAdditional = $data['prompt_additional'] ?? null;
                            $publishOnComplete = (bool) ($data['publish_on_complete'] ?? false);

                            $dispatchedCount = 0;
                            $skippedCount = 0;
                            $alreadyInQueueCount = 0;
                            $createdJobIds = [];

                            foreach ($records as $post) {
                                // Cek apakah post sedang aktif dalam antrean
                                $isAlreadyQueued = AiGenerationJob::where('post_id', $post->id)
                                    ->whereIn('status', ['pending', 'processing'])
                                    ->exists();

                                if ($isAlreadyQueued) {
                                    $alreadyInQueueCount++;
                                    continue;
                                }

                                $wordCount = str_word_count(strip_tags($post->content ?? ''));
                                if ($onlyEmpty && $wordCount >= 200) {
                                    $skippedCount++;
                                    continue;
                                }

                                $aiJob = AiGenerationJob::create([
                                    'post_id' => $post->id,
                                    'post_title' => $post->title,
                                    'status' => 'pending',
                                    'prompt_additional' => $promptAdditional,
                                    'publish_on_complete' => $publishOnComplete,
                                ]);

                                $createdJobIds[] = $aiJob->id;
                                $dispatchedCount++;
                            }

                            // Dispatch background batch parallel jobs in chunks of 25 (concurrency 25)
                            foreach (array_chunk($createdJobIds, 25) as $chunkIds) {
                                ProcessBatchAiQueueJob::dispatch($chunkIds, 25);
                            }

                            $notes = [];
                            if ($alreadyInQueueCount > 0) {
                                $notes[] = "{$alreadyInQueueCount} dilewati karena sedang dalam antrean";
                            }
                            if ($skippedCount > 0) {
                                $notes[] = "{$skippedCount} dilewati karena artikel sudah lengkap (>= 200 kata)";
                            }

                            $noteText = !empty($notes) ? ' (' . implode(', ', $notes) . ')' : '';

                            Notification::make()
                                ->title('Antrean Generate AI Dimulai')
                                ->body("{$dispatchedCount} post berhasil dimasukkan ke antrean.{$noteText}")
                                ->actions([
                                    Action::make('lihatAntrean')
                                        ->label('Buka Pemantau Antrean')
                                        ->url(route('filament.rahasia.resources.ai-generation-jobs.index')),
                                ])
                                ->success()
                                ->send();
                        }),

                    Action::make('publishBulk')
                        ->label('Publikasikan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Publikasikan Post Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status semua post yang dipilih menjadi Published?')
                        ->modalSubmitActionLabel('Ya, Publikasikan')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $updateData = ['status' => 'published'];
                                if (is_null($record->published_at)) {
                                    $updateData['published_at'] = now();
                                }
                                $record->update($updateData);
                                $count++;
                            }

                            Notification::make()
                                ->title('Post Berhasil Dipublikasikan')
                                ->body("{$count} post telah diubah statusnya menjadi Published.")
                                ->success()
                                ->send();
                        }),

                    Action::make('draftBulk')
                        ->label('Jadikan Draft Terpilih')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Jadikan Draft Post Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status semua post yang dipilih menjadi Draft?')
                        ->modalSubmitActionLabel('Ya, Jadikan Draft')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['status' => 'draft']);
                                $count++;
                            }

                            Notification::make()
                                ->title('Status Diubah ke Draft')
                                ->body("{$count} post telah diubah statusnya menjadi Draft.")
                                ->info()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null);
    }
}
