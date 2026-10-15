<?php

namespace App\Filament\Rahasia\Pages;

use App\Models\City;
use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageNavSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $navigationLabel = 'Menu Kota';

    protected static ?string $title = 'Pengaturan Menu Kota';

    protected static ?string $slug = 'settings/navigation';

    protected static ?int $navigationSort = 115;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['nav_city_ids' => City::navigation()->modelKeys()]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kota di navigasi publik')->schema([
                Select::make('nav_city_ids')
                    ->label('Kota yang ditampilkan')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->maxItems(8)
                    ->options(fn () => City::orderBy('name')->pluck('name', 'id'))
                    ->helperText('Maksimal 8 kota, sesuai urutan pilihan. Kosongkan untuk menyembunyikan menu kota. Berlaku di header, pintasan beranda, dan footer.'),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Simpan menu kota')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $state = $this->form->getState();
        validator($state, [
            'nav_city_ids' => ['nullable', 'array', 'max:8'],
            'nav_city_ids.*' => ['integer', 'distinct', 'exists:cities,id'],
        ])->validate();

        Setting::set('nav_city_ids', json_encode(array_values(array_map('intval', $state['nav_city_ids'] ?? []))));

        Notification::make()->title('Menu kota disimpan')->success()->send();
    }
}
