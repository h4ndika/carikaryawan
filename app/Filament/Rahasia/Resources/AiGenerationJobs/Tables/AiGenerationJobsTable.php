<?php

namespace App\Filament\Rahasia\Resources\AiGenerationJobs\Tables;

use App\Jobs\GeneratePostContentJob;
use App\Models\AiGenerationJob;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class AiGenerationJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('5s')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('post.title')
                    ->label('Post')
                    ->default(fn (AiGenerationJob $record) => $record->post_title ?? '-')
                    ->searchable()
                    ->url(fn (AiGenerationJob $record) => $record->post_id ? route('filament.rahasia.resources.posts.edit', ['record' => $record->post_id]) : null)
                    ->openUrlInNewTab()
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'info',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'processing' => 'Sedang Proses',
                        'pending' => 'Menunggu Antrean',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),

                TextColumn::make('prompt_additional')
                    ->label('Prompt Tambahan')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('publish_on_complete')
                    ->label('Auto Publish')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(),

                TextColumn::make('finished_at')
                    ->label('Selesai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M H:i:s')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Keterangan Error')
                    ->limit(30)
                    ->color('danger')
                    ->visible(fn (?AiGenerationJob $record) => $record?->status === 'failed')
                    ->tooltip(fn (AiGenerationJob $record) => $record->error_message),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Antrean',
                        'processing' => 'Sedang Proses',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Jalankan Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (AiGenerationJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (AiGenerationJob $record) {
                        $record->update([
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'finished_at' => null,
                        ]);

                        GeneratePostContentJob::dispatch($record->id);

                        Notification::make()
                            ->title('Job berhasil dimasukkan kembali ke antrean')
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail Error')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (AiGenerationJob $record) => !empty($record->error_message))
                    ->modalHeading('Rincian Pesan Error')
                    ->modalDescription(fn (AiGenerationJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('retryBulk')
                        ->label('Jalankan Ulang Terpilih')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->accessSelectedRecords()
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'pending',
                                    'error_message' => null,
                                    'started_at' => null,
                                    'finished_at' => null,
                                ]);
                                GeneratePostContentJob::dispatch($record->id);
                                $count++;
                            }

                            Notification::make()
                                ->title("$count Job berhasil dimasukkan kembali ke antrean")
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
