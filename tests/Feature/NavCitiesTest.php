<?php

use App\Filament\Rahasia\Pages\ManageNavSettings;
use App\Livewire\Search;
use App\Models\City;
use App\Models\Province;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Http::fake();
    $province = Province::create(['name' => 'Provinsi Uji', 'slug' => 'provinsi-uji']);
    $this->firstCity = City::create(['name' => 'Kota Pertama', 'slug' => 'kota-pertama', 'province_id' => $province->id]);
    $this->secondCity = City::create(['name' => 'Kota Kedua', 'slug' => 'kota-kedua', 'province_id' => $province->id]);
});

it('saves ordered city navigation through the admin form', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    Livewire::test(ManageNavSettings::class)
        ->set('data.nav_city_ids', [$this->secondCity->id, $this->firstCity->id])
        ->call('save')->assertHasNoErrors();

    expect(json_decode(Setting::get('nav_city_ids'), true))->toBe([$this->secondCity->id, $this->firstCity->id]);
    $this->get('/')->assertOk()->assertSeeInOrder([
        route('posts.list', ['category' => 'lowongan-kerja', 'city' => 'kota-kedua']),
        route('posts.list', ['category' => 'lowongan-kerja', 'city' => 'kota-pertama']),
    ]);
});

it('immediately removes deselected cities without a stale navigation cache', function () {
    Setting::set('nav_city_ids', json_encode([$this->firstCity->id]));
    $url = route('posts.list', ['category' => 'lowongan-kerja', 'city' => 'kota-pertama']);
    $this->get('/')->assertSee($url);
    Setting::set('nav_city_ids', '[]');
    $this->get('/')->assertOk()->assertDontSee($url)->assertSee('Cari loker');
});

it('ignores malformed settings and deleted or duplicate cities', function () {
    Setting::set('nav_city_ids', '{bad json');
    expect(City::navigation())->toHaveCount(0);
    Setting::set('nav_city_ids', '"not an array"');
    $this->get('/')->assertOk();
    Setting::set('nav_city_ids', json_encode([true, 1.0, $this->firstCity->id, $this->firstCity->id, 999999, [], 'bad']));
    expect(City::navigation()->modelKeys())->toBe([$this->firstCity->id]);
    $this->firstCity->delete();
    expect(City::navigation())->toHaveCount(0);
});

it('rejects nonexistent city selections', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    Livewire::test(ManageNavSettings::class)
        ->set('data.nav_city_ids', [999999])->call('save')->assertHasErrors();
    $this->assertDatabaseMissing('settings', ['key' => 'nav_city_ids']);
});

it('rejects duplicate cities and more than eight selections', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    $ids = [$this->firstCity->id, $this->secondCity->id];
    foreach (range(3, 9) as $number) {
        $ids[] = City::create(['name' => "Kota $number", 'slug' => "kota-$number", 'province_id' => $this->firstCity->province_id])->id;
    }

    Livewire::test(ManageNavSettings::class)
        ->set('data.nav_city_ids', $ids)->call('save')->assertHasErrors();
    Livewire::test(ManageNavSettings::class)
        ->set('data.nav_city_ids', [$ids[0], $ids[0]])->call('save')->assertHasErrors();
    $this->assertDatabaseMissing('settings', ['key' => 'nav_city_ids']);
});

it('renders real job metadata and keeps every home listing visible', function () {
    $post = publishedPost();
    $post->details()->createMany([
        ['meta_key' => 'nama_perusahaan', 'meta_value' => 'PT Perusahaan Nyata'],
        ['meta_key' => 'gaji', 'meta_value' => 'Rp 5.000.000 per bulan'],
        ['meta_key' => 'tipe_pekerjaan', 'meta_value' => 'Full time'],
    ]);
    $this->get('/')->assertOk()->assertSee($post->title)->assertSee('PT Perusahaan Nyata')
        ->assertSee('Rp 5.000.000 per bulan')->assertSee('Full time')->assertDontSee('Foto:');
    $this->get('/cari?city=bandung-kota&q=Admin')->assertOk()->assertSee($post->title);
    Livewire::test(Search::class)->set('q', 'tidak-cocok')->set('city', 'bandung-kota')
        ->call('resetFilters')->assertSet('q', '')->assertSet('city', '')->assertSee($post->title);
});
