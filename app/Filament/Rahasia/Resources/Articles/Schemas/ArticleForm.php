<?php

namespace App\Filament\Rahasia\Resources\Articles\Schemas;

use App\Models\Category;
use App\Models\City;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Artikel')
                    ->description('Tulis atau edit artikel editorial blog.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Artikel')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Textarea::make('excerpt')
                            ->label('Ringkasan (Excerpt)')
                            ->rows(3)
                            ->helperText('Tampil di kartu artikel dan meta description SEO.'),

                        RichEditor::make('content')
                            ->label('Isi Artikel (HTML)')
                            ->required()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('articles/attachments')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Klasifikasi & Media')
                    ->schema([
                        Select::make('city_id')
                            ->label('Kota Terkait')
                            ->options(
                                City::query()
                                    ->whereHas('posts', fn ($q) => $q->published())
                                    ->withCount(['posts' => fn ($q) => $q->published()])
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn ($city) => [$city->id => "{$city->name} ({$city->posts_count} tempat)"])
                            )
                            ->searchable()
                            ->nullable(),

                        Select::make('category_id')
                            ->label('Topik / Kategori')
                            ->options(Category::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        TextInput::make('featured_image_url')
                            ->label('URL Gambar Cover / Featured')
                            ->placeholder('https://images.unsplash.com/...')
                            ->helperText('Bisa menggunakan URL gambar dari Post atau unplash/pexels.')
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status Publikasi')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('published')
                            ->required(),
                    ])->columns(2),

                Section::make('Pengaturan SEO')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title (Opsional)'),
                        Textarea::make('meta_description')
                            ->label('Meta Description (Opsional)')
                            ->rows(2),
                    ]),
            ]);
    }
}

