<?php

namespace App\Filament\Rahasia\Resources\JobHarvests\Tables;

use App\Jobs\GeneratePostContentJob;
use App\Jobs\ProcessBatchAiQueueJob;
use App\Models\AiGenerationJob;
use App\Models\City;
use App\Models\Post;
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

class JobHarvestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Posisi & Perusahaan')
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->description(fn (Post $record): ?string => $record->website ? Str_limit($record->website, 45) : null),

                TextColumn::make('city.name')
                    ->label('Kota')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                IconColumn::make('content_status')
                    ->label('Konten AI')
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
                            ? "Artikel ulasan lengkap ({$words} kata)"
                            : ($words > 0 ? "Belum lengkap / deskripsi awal ({$words} kata)" : 'Konten masih kosong');
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Waktu Terbit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('expired_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y')
                    ->placeholder('Tidak ada')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                TrashedFilter::make(),

                SelectFilter::make('status')
                    ->options([
                        'published' => 'Published (Tayang)',
                        'draft' => 'Draft (Menunggu AI / Belum Tayang)',
                    ]),

                Filter::make('content_status')
                    ->form([
                        Select::make('has_content')
                            ->label('Status Artikel AI')
                            ->options([
                                'filled' => 'Lengkap (>= 200 kata)',
                                'empty' => 'Belum Lengkap / Draft (< 200 kata)',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                ($data['has_content'] ?? null) === 'filled',
                                fn (Builder $q) => $q->whereNotNull('content')
                                    ->where('content', '!=', '')
                                    ->whereRaw("(LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1) >= 200")
                            )
                            ->when(
                                ($data['has_content'] ?? null) === 'empty',
                                fn (Builder $q) => $q->where(function ($sub) {
                                    $sub->whereNull('content')
                                        ->orWhere('content', '')
                                        ->orWhereRaw("(LENGTH(TRIM(content)) - LENGTH(REPLACE(TRIM(content), ' ', '')) + 1) < 200");
                                })
                            );
                    }),

                SelectFilter::make('city_id')
                    ->relationship('city', 'name')
                    ->label('Kota')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Dibuat dari'),
                        DatePicker::make('created_until')->label('Dibuat sampai'),
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
                Action::make('viewSite')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Post $record): string => route('posts.show', $record->slug))
                    ->openUrlInNewTab(),

                Action::make('generateAi')
                    ->label('Generate AI')
                    ->icon('heroicon-o-sparkles')
                    ->color('amber')
                    ->modalHeading(fn (Post $record) => "Generate Konten AI: {$record->title}")
                    ->modalDescription('Proses ini akan menghasilkan artikel ulasan lengkap, tips melamar, dan detail loker via background queue.')
                    ->modalSubmitActionLabel('Jalankan AI')
                    ->form([
                        Textarea::make('prompt_additional')
                            ->label('Prompt Tambahan (Opsional)')
                            ->placeholder('Contoh: Tekankan kualifikasi fresh graduate dan tips interview.')
                            ->rows(3),
                        Toggle::make('publish_on_complete')
                            ->label('Otomatis ubah status ke Published setelah selesai')
                            ->default(true),
                    ])
                    ->action(function (Post $record, array $data) {
                        $isAlreadyQueued = AiGenerationJob::where('post_id', $record->id)
                            ->whereIn('status', ['pending', 'processing'])
                            ->exists();

                        if ($isAlreadyQueued) {
                            Notification::make()
                                ->title('Loker Sudah di Antrean AI')
                                ->body("Post '{$record->title}' sedang diproses antrean AI.")
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
                            'publish_on_complete' => (bool) ($data['publish_on_complete'] ?? true),
                        ]);

                        GeneratePostContentJob::dispatch($aiJob->id);

                        Notification::make()
                            ->title('Antrean AI Dibuat')
                            ->body("Generate konten untuk '{$record->title}' telah dikirim ke queue.")
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
                    Action::make('generateAiBulk')
                        ->label('Generate Konten AI (Batch)')
                        ->icon('heroicon-o-sparkles')
                        ->color('amber')
                        ->accessSelectedRecords()
                        ->modalHeading('Generate Konten AI untuk Loker Terpilih')
                        ->modalDescription('Menghasilkan ulasan artikel loker otomatis untuk postingan yang dipilih.')
                        ->modalSubmitActionLabel('Mulai Antrean AI')
                        ->form([
                            Toggle::make('only_empty')
                                ->label('Hanya proses loker yang belum memiliki artikel lengkap (< 200 kata)')
                                ->default(true),
                            Textarea::make('prompt_additional')
                                ->label('Prompt Tambahan (Opsional)')
                                ->rows(3),
                            Toggle::make('publish_on_complete')
                                ->label('Otomatis ubah status ke Published setelah selesai')
                                ->default(true),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $onlyEmpty = (bool) ($data['only_empty'] ?? true);
                            $promptAdditional = $data['prompt_additional'] ?? null;
                            $publishOnComplete = (bool) ($data['publish_on_complete'] ?? true);

                            $dispatchedCount = 0;
                            $skippedCount = 0;
                            $alreadyInQueueCount = 0;
                            $createdJobIds = [];

                            foreach ($records as $post) {
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

                            foreach (array_chunk($createdJobIds, 25) as $chunkIds) {
                                ProcessBatchAiQueueJob::dispatch($chunkIds, 25);
                            }

                            Notification::make()
                                ->title('Antrean AI Dimulai')
                                ->body("{$dispatchedCount} loker berhasil dimasukkan ke antrean AI.")
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
                                ->title('Loker Berhasil Dipublikasikan')
                                ->body("{$count} loker diubah statusnya menjadi Published.")
                                ->success()
                                ->send();
                        }),

                    Action::make('draftBulk')
                        ->label('Jadikan Draft Terpilih')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update(['status' => 'draft']);
                                $count++;
                            }

                            Notification::make()
                                ->title('Status Diubah ke Draft')
                                ->body("{$count} loker diubah ke Draft.")
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
