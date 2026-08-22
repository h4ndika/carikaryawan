<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Tables;

use App\Models\ApifyAccount;
use App\Services\ApifyScraperService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
