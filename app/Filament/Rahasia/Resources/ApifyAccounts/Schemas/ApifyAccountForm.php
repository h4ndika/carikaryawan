<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApifyAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->label('Nama / Label Akun')
                    ->placeholder('Contoh: Akun Utama, Akun Cadangan 1')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email Akun (Opsional)')
                    ->email()
                    ->placeholder('Contoh: user@domain.com')
                    ->maxLength(255),

                TextInput::make('token')
                    ->label('Apify API Token')
                    ->placeholder('apify_api_...')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull()
                    ->helperText('Dapatkan token dari Apify Console -> Settings -> Integrations -> API Tokens.'),

                Select::make('status')
                    ->label('Status Akun')
                    ->options([
                        'active' => 'Aktif (Siap Pakai)',
                        'exhausted' => 'Limit Tercapai (Exhausted)',
                        'invalid' => 'Tidak Valid / Error',
                        'disabled' => 'Nonaktifkan Manual',
                    ])
                    ->default('active')
                    ->required(),

                TextInput::make('usage_count')
                    ->label('Total Penggunaan Scrape')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('notes')
                    ->label('Catatan Tambahan')
                    ->placeholder('Catatan kuota, tanggal pembuatan, dll.')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
