<?php

namespace App\Filament\Rahasia\Resources\Posts\Pages;

use App\Filament\Rahasia\Resources\Posts\PostResource;
use App\Models\Category;
use App\Services\GoogleMapsImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importJobs')
                ->label('Import Loker (JSON)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Loker (JSON)')
                ->modalDescription('Upload file JSON hasil scraper loker (JobStreet/Glints). Post yang baru akan otomatis berstatus draft.')
                ->modalSubmitActionLabel('Mulai Import')
                ->form([
                    FileUpload::make('file')
                        ->label('File JSON')
                        ->acceptedFileTypes(['application/json', 'text/plain', 'text/json'])
                        ->disk('local')
                        ->directory('temp-imports')
                        ->required()
                        ->helperText('Upload file .json berformat hasil scraper loker.'),
                    Select::make('default_category_id')
                        ->label('Kategori Default')
                        ->options(Category::jobs()->pluck('name', 'id'))
                        ->searchable()
                        ->helperText('Digunakan bila kategori dari data tidak ditemukan di database.'),
                    Toggle::make('skip_duplicates')
                        ->label('Lewati Duplikat (Berdasarkan Place ID)')
                        ->default(false)
                        ->helperText('Jika nonaktif, post yang memiliki Place ID sama akan diperbarui datanya.'),
                ])
                ->action(function (array $data, GoogleMapsImportService $service): void {
                    $relativeFilePath = $data['file'] ?? null;
                    if (! $relativeFilePath) {
                        Notification::make()
                            ->title('Gagal mengimpor')
                            ->body('File tidak ditemukan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $absoluteFilePath = Storage::disk('local')->path($relativeFilePath);

                    if (! file_exists($absoluteFilePath)) {
                        Notification::make()
                            ->title('Gagal mengimpor')
                            ->body('File upload tidak ditemukan di server.')
                            ->danger()
                            ->send();
                        return;
                    }

                    if (! empty($data['default_category_id'])) {
                        $service->setDefaultCategoryId((int) $data['default_category_id']);
                    }

                    $service->setSkipDuplicates((bool) ($data['skip_duplicates'] ?? false));

                    @set_time_limit(300);

                    $stats = $service->importFromFile($absoluteFilePath);

                    // Clean up temporary uploaded file
                    try {
                        Storage::disk('local')->delete($relativeFilePath);
                    } catch (\Throwable $e) {
                        // ignore cleanup failure
                    }

                    Notification::make()
                        ->title('Import Loker Selesai')
                        ->body("Hasil Import:\n• {$stats['imported']} Baru\n• {$stats['updated']} Diperbarui\n• {$stats['skipped']} Dilewati\n• {$stats['failed']} Gagal")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
