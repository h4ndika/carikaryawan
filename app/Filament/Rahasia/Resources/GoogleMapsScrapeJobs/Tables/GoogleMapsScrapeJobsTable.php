<?php

namespace App\Filament\Rahasia\Resources\GoogleMapsScrapeJobs\Tables;

use App\Jobs\RunGoogleMapsScrapeJob;
use App\Models\ApifyAccount;
use App\Models\GoogleMapsScrapeJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GoogleMapsScrapeJobsTable
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

                TextColumn::make('engine')
                    ->label('Mesin')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'jobstreet' => 'success',
                        'glints' => 'info',
                        'google-jobs' => 'warning',
                        'instagram' => 'danger',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'jobstreet' => 'JobStreet',
                        'glints' => 'Glints',
                        'google-jobs' => 'Google Jobs',
                        'instagram' => 'Instagram',
                        default => 'Google Maps',
                    }),

                TextColumn::make('query')
                    ->label('Kata Kunci / Query')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (GoogleMapsScrapeJob $record) => $record->location ? "Lokasi: {$record->location}" : null)
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->default('Otomatis (Deteksi Maps)')
                    ->badge()
                    ->color('info'),

                TextColumn::make('city.name')
                    ->label('Kota')
                    ->default('Otomatis (Alamat)')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('apifyAccount.label')
                    ->label('Akun Digunakan')
                    ->default(fn (GoogleMapsScrapeJob $record) => $record->apify_account_id ? 'Akun #' . $record->apify_account_id : 'Rotasi Otomatis')
                    ->badge()
                    ->color('secondary')
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'scraping' => 'info',
                        'importing' => 'primary',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'scraping' => 'Sedang Ekstraksi',
                        'importing' => 'Menyimpan ke MySQL',
                        'pending' => 'Menunggu Antrean',
                        'failed' => 'Gagal',
                        default => ucfirst($state),
                    }),

                TextColumn::make('stats')
                    ->label('Hasil Masuk')
                    ->state(function (GoogleMapsScrapeJob $record) {
                        if ($record->status === 'completed') {
                            return "{$record->total_imported} Baru / {$record->total_updated} Update";
                        }
                        if ($record->status === 'importing' || $record->status === 'scraping') {
                            return "Scraped: {$record->total_scraped}";
                        }
                        return "Target: {$record->target_limit}";
                    })
                    ->badge()
                    ->color('success'),

                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('finished_at')
                    ->label('Selesai')
                    ->dateTime('d M H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M H:i:s')
                    ->sortable(),

                TextColumn::make('error_message')
                    ->label('Keterangan / Rincian')
                    ->wrap()
                    ->limit(120)
                    ->color(fn (GoogleMapsScrapeJob $record) => match ($record->status) {
                        'failed' => 'danger',
                        'completed' => 'gray',
                        default => 'secondary',
                    })
                    ->placeholder('-')
                    ->tooltip(fn (GoogleMapsScrapeJob $record) => $record->error_message),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Antrean',
                        'scraping' => 'Sedang Ekstraksi',
                        'importing' => 'Menyimpan ke MySQL',
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Ulangi Ekstraksi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (GoogleMapsScrapeJob $record) => in_array($record->status, ['failed', 'completed']))
                    ->action(function (GoogleMapsScrapeJob $record) {
                        // Pilih akun aktif acak (diutamakan selain akun yang sebelumnya jika ada akun lain)
                        $randomAccount = ApifyAccount::active()
                            ->when($record->apify_account_id, fn ($q) => $q->where('id', '!=', $record->apify_account_id))
                            ->inRandomOrder()
                            ->first()
                            ?? ApifyAccount::active()->inRandomOrder()->first();

                        $record->update([
                            'apify_account_id' => $randomAccount?->id,
                            'status' => 'pending',
                            'error_message' => null,
                            'started_at' => null,
                            'finished_at' => null,
                        ]);

                        RunGoogleMapsScrapeJob::dispatch($record->id);

                        $accountLabel = $randomAccount ? ($randomAccount->label ?: 'Akun #' . $randomAccount->id) : 'Rotasi Otomatis';

                        Notification::make()
                            ->title('Task ekstraksi dikirim kembali ke antrean')
                            ->body("Akun Apify dialihkan ke: {$accountLabel}")
                            ->success()
                            ->send();
                    }),

                Action::make('viewError')
                    ->label('Detail Error')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (GoogleMapsScrapeJob $record) => !empty($record->error_message))
                    ->modalHeading('Rincian Error Ekstraksi')
                    ->modalDescription(fn (GoogleMapsScrapeJob $record) => $record->error_message)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retrySelected')
                        ->label('Ulangi Ekstraksi Terpilih (Acak Akun)')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Ulangi Ekstraksi Data Terpilih')
                        ->modalDescription('Semua task terpilih akan dikirim kembali ke antrean dan dialihkan menggunakan akun Apify aktif secara acak/rotasi baru.')
                        ->modalSubmitActionLabel('Ya, Ulangi Sekarang')
                        ->action(function (Collection $records) {
                            $activeAccounts = ApifyAccount::active()->get();
                            $count = 0;

                            foreach ($records as $record) {
                                $filtered = $activeAccounts->where('id', '!=', $record->apify_account_id);
                                $randomAccount = $filtered->isNotEmpty()
                                    ? $filtered->random()
                                    : ($activeAccounts->isNotEmpty() ? $activeAccounts->random() : null);

                                $record->update([
                                    'apify_account_id' => $randomAccount?->id,
                                    'status' => 'pending',
                                    'error_message' => null,
                                    'started_at' => null,
                                    'finished_at' => null,
                                ]);

                                RunGoogleMapsScrapeJob::dispatch($record->id);
                                $count++;
                            }

                            Notification::make()
                                ->title("{$count} task ekstraksi dikirim kembali ke antrean")
                                ->body('Semua task terpilih telah dialihkan ke akun Apify aktif secara acak.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
