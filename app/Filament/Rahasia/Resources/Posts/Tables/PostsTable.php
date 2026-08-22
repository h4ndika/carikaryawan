<?php

namespace App\Filament\Rahasia\Resources\Posts\Tables;

use App\Jobs\GeneratePostContentJob;
use App\Models\AiGenerationJob;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
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
                    ->state(fn (Post $record): bool => !empty(trim(strip_tags($record->content ?? ''))))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (Post $record): string => !empty(trim(strip_tags($record->content ?? ''))) ? 'Konten terisi' : 'Konten masih kosong'),

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

                Filter::make('content_status')
                    ->form([
                        \Filament\Forms\Components\Select::make('has_content')
                            ->label('Ketersediaan Konten')
                            ->options([
                                'filled' => 'Sudah Ada Konten',
                                'empty' => 'Konten Masih Kosong',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['has_content'] === 'filled',
                                fn (Builder $q) => $q->whereNotNull('content')->where('content', '!=', '')
                            )
                            ->when(
                                $data['has_content'] === 'empty',
                                fn (Builder $q) => $q->where(fn ($sub) => $sub->whereNull('content')->orWhere('content', ''))
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
                    ->label('City'),

                SelectFilter::make('province_id')
                    ->relationship('province', 'name')
                    ->label('Province'),
            ])
            ->recordActions([
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
                        $aiJob = AiGenerationJob::create([
                            'post_id' => $record->id,
                            'post_title' => $record->title,
                            'status' => 'pending',
                            'prompt_additional' => $data['prompt_additional'] ?? null,
                            'publish_on_complete' => (bool) ($data['publish_on_complete'] ?? false),
                        ]);

                        GeneratePostContentJob::dispatch($aiJob->id);

                        Notification::make()
                            ->title('Job AI Berhasil Ditambahkan ke Antrean')
                            ->body("Post '{$record->title}' sedang diproses di latar belakang.")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('lihatAntrean')
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
                        ->label('Generate Konten Pakai AI')
                        ->icon('heroicon-o-sparkles')
                        ->color('amber')
                        ->accessSelectedRecords()
                        ->modalHeading('Generate Konten AI untuk Post Terpilih')
                        ->modalDescription('Membuat konten, excerpt, dan detail tempat secara otomatis untuk post yang dipilih menggunakan background job queue.')
                        ->modalSubmitActionLabel('Mulai Antrean AI')
                        ->form([
                            Toggle::make('only_empty')
                                ->label('Hanya proses post yang kontennya masih kosong')
                                ->default(true)
                                ->helperText('Jika aktif, post yang sudah memiliki konten tidak akan ditimpa.'),
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

                            foreach ($records as $post) {
                                if ($onlyEmpty && !empty(trim(strip_tags($post->content ?? '')))) {
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

                                GeneratePostContentJob::dispatch($aiJob->id);
                                $dispatchedCount++;
                            }

                            Notification::make()
                                ->title('Antrean Generate AI Dimulai')
                                ->body("{$dispatchedCount} post berhasil dimasukkan ke antrean." . ($skippedCount > 0 ? " ({$skippedCount} dilewati karena sudah ada konten)" : ''))
                                ->actions([
                                    \Filament\Notifications\Actions\Action::make('lihatAntrean')
                                        ->label('Buka Pemantau Antrean')
                                        ->url(route('filament.rahasia.resources.ai-generation-jobs.index')),
                                ])
                                ->success()
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
