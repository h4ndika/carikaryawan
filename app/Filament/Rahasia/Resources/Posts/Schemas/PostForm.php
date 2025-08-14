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

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->default('Deskripsikan hotel ini dengan format Tentang, Fasilitas, Lokasi, Harga')
                    ->hintAction(
                        Action::make('generateContent')
                            ->label('Generate AI')
                            ->icon('heroicon-o-sparkles')
                            ->color('primary')
                            ->action(function (\Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) {
                                $judul = $get('name');
                                $cityName = optional(\App\Models\City::find($get('city_id')))->name;
                                $provinceName = optional(\App\Models\Province::find($get('province_id')))->name;
                                $promptUser = $get('ai_prompt');

                                $finalPrompt = "Buatkan artikel tentang \"$judul\" yang berlokasi di $cityName, $provinceName. ".
                                   "Gunakan format berikut: $promptUser. ".
                                   "Tulis dengan bahasa Indonesia yang natural dan menarik.".
                                   "Berikan output hanya dalam format HTML tanpa tambahan teks lain seperti penutup atau salam.";

                                $response = OpenAI::chat()->create([
                                    'model' => 'gpt-4o-mini',
                                    'messages' => [
                                        ['role' => 'system', 'content' => 'Kamu adalah penulis artikel profesional dan membuat konten HTML untuk website.'],
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

                                $set('content', trim($generated));
                            })
                        ),

                RichEditor::make('content')
                    ->label('Konten')
                    ->columnSpanFull()
                    ->required(),
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

            ]);
    }
}
