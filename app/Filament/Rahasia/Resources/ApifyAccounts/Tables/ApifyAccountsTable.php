<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Tables;

use App\Models\ApifyAccount;
use App\Services\ApifyScraperService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ApifyAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_used_at', 'asc')
            ->columns([
                TextColumn::make('label')
                    ->label('Nama Akun')
                    ->default(fn (ApifyAccount $record) => 'Akun #' . $record->id)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->default('-')
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'exhausted' => 'warning',
                        'invalid' => 'danger',
                        'disabled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'exhausted' => 'Limit Habis',
                        'invalid' => 'Tidak Valid',
                        'disabled' => 'Nonaktif',
                        default => ucfirst($state),
                    }),

                TextColumn::make('usage_count')
                    ->label('Dipakai')
                    ->suffix(' kali')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('last_used_at')
                    ->label('Terakhir Digunakan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Aktif',
                        'exhausted' => 'Limit Habis',
                        'invalid' => 'Tidak Valid',
                        'disabled' => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                Action::make('testConnection')
                    ->label('Cek Token')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->action(function (ApifyAccount $record, ApifyScraperService $service) {
                        $result = $service->testToken($record->token);

                        if ($result['valid']) {
                            $record->update(['status' => 'active']);
                            Notification::make()
                                ->title('Token Apify Valid')
                                ->body("Username: {$result['username']} | Plan: {$result['plan']}")
                                ->success()
                                ->send();
                        } else {
                            if (str_contains($result['message'], 'Exhausted') || str_contains($result['message'], '402')) {
                                $record->update(['status' => 'exhausted']);
                            } else {
                                $record->update(['status' => 'invalid']);
                            }
                            Notification::make()
                                ->title('Gagal Validasi')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('activateBulk')
                        ->label('Aktifkan Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Aktifkan Akun Apify Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status semua akun yang dipilih menjadi Aktif?')
                        ->modalSubmitActionLabel('Ya, Aktifkan')
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['status' => 'active']);

                            Notification::make()
                                ->title('Status Akun Diaktifkan')
                                ->body("{$count} akun Apify berhasil diubah menjadi Aktif.")
                                ->success()
                                ->send();
                        }),

                    Action::make('deactivateBulk')
                        ->label('Nonaktifkan Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->accessSelectedRecords()
                        ->requiresConfirmation()
                        ->modalHeading('Nonaktifkan Akun Apify Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin mengubah status semua akun yang dipilih menjadi Nonaktif?')
                        ->modalSubmitActionLabel('Ya, Nonaktifkan')
                        ->action(function (Collection $records) {
                            $count = $records->count();
                            $records->each->update(['status' => 'disabled']);

                            Notification::make()
                                ->title('Status Akun Dinonaktifkan')
                                ->body("{$count} akun Apify berhasil dinonaktifkan.")
                                ->warning()
                                ->send();
                        }),

                    Action::make('changeStatusBulk')
                        ->label('Ubah Status...')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('primary')
                        ->accessSelectedRecords()
                        ->form([
                            Select::make('status')
                                ->label('Pilih Status Baru')
                                ->options([
                                    'active' => 'Aktif (Siap Pakai)',
                                    'disabled' => 'Nonaktif',
                                    'exhausted' => 'Limit Habis (Exhausted)',
                                    'invalid' => 'Tidak Valid / Error',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = $records->count();
                            $status = $data['status'];
                            $records->each->update(['status' => $status]);

                            Notification::make()
                                ->title('Status Akun Berhasil Diperbarui')
                                ->body("{$count} akun Apify berhasil diperbarui statusnya.")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
