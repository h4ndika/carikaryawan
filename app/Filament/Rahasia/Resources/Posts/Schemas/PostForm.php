<?php

namespace App\Filament\Rahasia\Resources\Posts\Schemas;

use App\Models\Category;
use App\Models\City;
use App\Models\Province;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Masukkan data utama untuk post ini.')
                    ->schema([
                        TextInput::make('title')->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('slug', Str::slug($state));
                                }),
                        TextInput::make('slug'),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        Select::make('city_id')
                            ->label('City')
                            ->options(City::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $city = City::find($state);
                                    if ($city && $city->province_id) {
                                        $set('province_id', $city->province_id);
                                    }
                                }
                            }),
                        Select::make('province_id')
                            ->label('Province')
                            ->options(Province::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        RichEditor::make('ai_prompt')
                            ->label('AI Prompt')
                            ->default('Deskripsikan dengan format Tentang, Fasilitas, Lokasi, Harga')
                            ->hintAction(
                                Action::make('generateContent')
                                    ->label('Generate AI')
                                    ->icon('heroicon-o-sparkles')
                                    ->color('primary')
                                    ->action(function (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) {
                                        $judul = $get('title');
                                        $cityName = optional(\App\Models\City::find($get('city_id')))->name;
                                        $provinceName = optional(\App\Models\Province::find($get('province_id')))->name;
                                        $category = \App\Models\Category::find($get('category_id'))?->name ?? '';
                                        $promptUser = $get('ai_prompt');

                                        $finalPrompt = "Buatkan artikel dengan Judul : \"$judul\", yang berlokasi di $cityName, $provinceName. ".
                                        "Artikel ini kategorinya $category".
                                        "Tulis dengan bahasa Indonesia yang natural dan menarik.".
                                        "Format artikel dalam HTML, jangan gunakan blok markdown atau backticks. Dan tanpa tambahan teks lain seperti penutup atau salam.".
                                        "Tambahkan juga data detail dalam JSON di akhir output, dibungkus tag <json>...</json> sesuai kategori, misal:".
                                        "- Kalau kategori seperti Hotel, Tempat Makan dll : harga, lokasi, jam_buka, fasilitas".
                                        "- Kalau kategori Lowongan Kerja: posisi, gaji, lokasi, kualifikasi, deskripsi_pekerjaan".
                                        "- dan selanjutnya tolong pikirkan key nya berdasarkan kategori lainnya".
                                        "Dengan Format Universal seperti ini :".
                                        "{
                                                \"key1\": \"value1\",
                                                \"key2\": \"value2\",
                                                \"key3\": \"value3\",
                                                \"key4\": \"value4\",
                                            }".
                                        "Prompt tambahan: $promptUser";

                                        $response = OpenAI::chat()->create([
                                            'model' => 'gpt-4o-mini',
                                            'messages' => [
                                                ['role' => 'system', 'content' => 'Kamu adalah penulis artikel SEO profesional dan membuat konten HTML untuk website.'],
                                                ['role' => 'user', 'content' => $finalPrompt],
                                            ],
                                            'temperature' => 0.7,
                                        ]);

                                        $generated = $response->choices[0]->message->content ?? '';
                                        try {
                                            // Hilangkan blok ```html ... ```
                                            $generated = preg_replace('/^```html\s*/', '', $generated); // hapus pembuka
                                            $generated = preg_replace('/```$/', '', $generated);       // hapus penutup

                                            // Hilangkan kalimat penutup yang tidak diinginkan
                                            $generated = preg_replace('/Semoga.*$/is', '', $generated);
                                        } catch (\Throwable $th) {
                                            //throw $th;
                                        }

                                        try {
                                            // Ambil bagian JSON
                                            preg_match('/<json>(.*?)<\/json>/s', $generated, $jsonMatch);
                                            $detailsArray = [];
                                            if (!empty($jsonMatch[1])) {
                                                $jsonData = json_decode(trim($jsonMatch[1]), true);
                                                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                                                    foreach ($jsonData as $key => $value) {
                                                        $detailsArray[] = [
                                                            'meta_key'   => $key,
                                                            'meta_value' => is_array($value) ? implode(", ", $value) : $value,
                                                        ];
                                                    }
                                                }
                                                // Hapus JSON dari HTML
                                                $generated = preg_replace('/<json>.*<\/json>/s', '', $generated);
                                            }
                                        } catch (\Throwable $th) {
                                            //throw $th;
                                        }

                                        $set('content', trim($generated));
                                        $set('details', $detailsArray);
                                    })
                                ),

                        RichEditor::make('content')
                            ->label('Konten')
                            ->columnSpanFull()
                            ->required(),
                        Repeater::make('details')
                            ->label('Detail Tambahan')
                            ->schema([
                                TextInput::make('meta_key')->label('Key')->required(),
                                Textarea::make('meta_value')->label('Value')->rows(2),
                            ])
                            ->columns(2)
                            ->grid(2)
                            ->minItems(0)
                            ->columnSpanFull()
                            ->dehydrated(),
                        Textarea::make('excerpt')
                            ->label('Excerpt'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Gambar')
                    ->description('Upload gambar untuk post ini.')
                    ->schema([

                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
