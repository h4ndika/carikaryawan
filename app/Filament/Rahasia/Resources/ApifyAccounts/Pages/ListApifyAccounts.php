<?php

namespace App\Filament\Rahasia\Resources\ApifyAccounts\Pages;

use App\Filament\Rahasia\Resources\ApifyAccounts\ApifyAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApifyAccounts extends ListRecords
{
    protected static string $resource = ApifyAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importBatchTokens')
                ->label('Import API Key (Batch/Bulk)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Banyak API Key Apify Sekaligus')
                ->modalDescription('Paste daftar API Key Apify (per baris atau format email|token) dari Google Maps Extractor.')
                ->form([
                    \Filament\Forms\Components\Textarea::make('raw_tokens')
                        ->label('Daftar API Key / Token')
                        ->placeholder("apify_api_xxx...\napify_api_yyy...\natau user@mail.com|apify_api_zzz...")
                        ->rows(10)
                        ->required()
                        ->helperText('Satu API Key per baris. Sistem otomatis mendeteksi format token biasa atau email|token, menguji validitasnya, dan melewati duplikat.'),

                    \Filament\Forms\Components\Toggle::make('auto_test')
                        ->label('Uji Validitas Token ke Apify Saat Import')
                        ->default(true)
                        ->helperText('Jika aktif, sistem akan langsung mengecek ke API Apify untuk memastikan token valid dan mendapatkan info username/email.'),
                ])
                ->action(function (array $data, \App\Services\ApifyScraperService $apifyService) {
                    $raw = trim($data['raw_tokens'] ?? '');
                    $autoTest = (bool) ($data['auto_test'] ?? true);

                    if (empty($raw)) {
                        return;
                    }

                    $lines = preg_split('/\r\n|\r|\n/', $raw);
                    $imported = 0;
                    $skipped = 0;
                    $failed = 0;

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) {
                            continue;
                        }

                        $email = null;
                        $token = $line;

                        // Support format: email|token or email:token
                        if (str_contains($line, '|')) {
                            [$email, $token] = array_map('trim', explode('|', $line, 2));
                        } elseif (str_contains($line, ':') && !str_starts_with($line, 'http')) {
                            [$email, $token] = array_map('trim', explode(':', $line, 2));
                        }

                        // Sanitize token (hapus kutip jika ada)
                        $token = trim($token, " \t\n\r\0\x0B\"'");

                        if (empty($token)) {
                            continue;
                        }

                        // Cek apakah token sudah ada
                        $exists = \App\Models\ApifyAccount::where('token', $token)->exists();
                        if ($exists) {
                            $skipped++;
                            continue;
                        }

                        $status = 'active';
                        $label = 'Imported ' . date('d M Y');

                        if ($autoTest) {
                            $test = $apifyService->testToken($token);
                            if ($test['valid']) {
                                if (!empty($test['email'])) {
                                    $email = $test['email'];
                                }
                                if (!empty($test['username'])) {
                                    $label = $test['username'];
                                }
                            } else {
                                if (str_contains($test['message'], 'limit') || str_contains($test['message'], 'Exhausted')) {
                                    $status = 'exhausted';
                                } else {
                                    $status = 'invalid';
                                }
                            }
                        }

                        if (!$email) {
                            $email = 'apify_' . substr(md5($token), 0, 8) . '@generated.local';
                        }

                        try {
                            \App\Models\ApifyAccount::create([
                                'label' => $label,
                                'email' => $email,
                                'token' => $token,
                                'status' => $status,
                                'usage_count' => 0,
                                'notes' => 'Di-import via Bulk Import pada ' . now(),
                            ]);
                            $imported++;
                        } catch (\Throwable $e) {
                            $failed++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Import API Key Selesai')
                        ->body("Berhasil disimpan: {$imported} akun baru. Di-skip (duplikat): {$skipped}. Gagal: {$failed}.")
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Tambah Akun Apify (Satu per satu)'),
        ];
    }
}
