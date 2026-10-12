<?php

use App\Ai\CariKaryawanAgent;
use App\Filament\Rahasia\Pages\ManageThreadsSettings;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use App\Models\Setting;
use App\Services\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sleep::fake();
    Http::preventStrayRequests();
    Setting::set('threads_user_id', 'test-user');
    Setting::set('threads_access_token', 'test-token');
    Setting::set('threads_chain_mode_enabled', true);
    Setting::set('threads_chain_posts', 4);
    $this->containers = [];
    $this->responses = null;
    Http::fake(function ($request) {
        if ($this->responses !== null) {
            return ($this->responses)($request);
        }
        if (str_ends_with($request->url(), '/threads_publish')) {
            return Http::response(['id' => 'published-' . $request['creation_id']]);
        }
        $this->containers[] = $request->data();

        return Http::response(['id' => 'container-' . count($this->containers)]);
    });
});

function threadsChainItem(): Post
{
    $province = Province::firstOrCreate(['slug' => 'jawa-barat'], ['name' => 'Jawa Barat']);
    $city = City::firstOrCreate(['slug' => 'bandung-kota'], ['name' => 'Kota Bandung', 'province_id' => $province->id]);
    $category = Category::firstOrCreate(['slug' => 'lowongan-kerja'], ['name' => 'Lowongan Kerja']);

    return Post::create([
        'title' => 'Info Bandung', 'slug' => 'info-bandung', 'status' => 'published',
        'category_id' => $category->id, 'city_id' => $city->id, 'province_id' => $province->id,
        'excerpt' => 'Informasi singkat.',
    ]);
}

function publishThreadsChainItem(Post $item): array
{
    return (new ThreadsService)->publishPost($item);
}

it('publishes connected replies with the link only in the last post', function (int $count) {
    Setting::set('threads_chain_posts', $count);
    $parts = array_map(fn ($i) => "Lanjutan cerita {$i} yang saling menyambung.", range(1, $count));
    CariKaryawanAgent::fake([json_encode($parts)])->preventStrayPrompts();
    $item = threadsChainItem();

    expect(publishThreadsChainItem($item)['id'])->toBe('published-container-1');
    expect($this->containers)->toHaveCount($count);
    foreach ($this->containers as $i => $container) {
        expect(mb_strlen($container['text']))->toBeLessThanOrEqual(500);
        if ($i === 0) {
            expect($container)->not->toHaveKey('reply_to_id');
        } else {
            expect($container['reply_to_id'])->toBe('published-container-' . $i);
        }
        if ($i === $count - 1) {
            expect($container['text'])->toContain($item->url());
        } else {
            expect($container['text'])->not->toContain('http');
        }
    }
    expect($this->containers[0]['media_type'])->toBe('IMAGE');
    expect($item->fresh()->posted_to_threads_at)->not->toBeNull();
    CariKaryawanAgent::assertPromptedTimes(1);
    Http::assertSentCount($count * 2);
})->with([3, 4, 6]);

it('keeps the two-post flow when chain mode is off', function () {
    Setting::set('threads_chain_mode_enabled', false);
    CariKaryawanAgent::fake(['Texte utama lama.'])->preventStrayPrompts();
    $item = threadsChainItem();

    publishThreadsChainItem($item);

    expect($this->containers)->toHaveCount(2)
        ->and($this->containers[0]['text'])->toBe('Texte utama lama.')
        ->and($this->containers[1]['reply_to_id'])->toBe('published-container-1')
        ->and($this->containers[1]['text'])->toContain($item->url());
    CariKaryawanAgent::assertPromptedTimes(1);
});

it('falls back before publishing when AI chain output is invalid', function (string $response) {
    CariKaryawanAgent::fake([$response, 'Teks utama fallback.'])->preventStrayPrompts();
    $item = threadsChainItem();

    publishThreadsChainItem($item);

    expect($this->containers)->toHaveCount(2)
        ->and($this->containers[0]['text'])->toBe('Teks utama fallback.')
        ->and($this->containers[1]['text'])->toContain($item->url());
    CariKaryawanAgent::assertPromptedTimes(2);
})->with([
    'wrong count' => ['["Satu", "Dua"]'],
    'URL' => ['["Satu", "https://example.com", "Tiga", "Empat"]'],
    'bare URL' => ['["Satu", "example.com", "Tiga", "Empat"]'],
    'object' => ['{"0":"Satu","1":"Dua","2":"Tiga","3":"Empat"}'],
    'non-string' => ['["Satu", 12, "Tiga", "Empat"]'],
    'empty' => ['["Satu", "  ", "Tiga", "Empat"]'],
    'numbering' => ['["1/4 Satu", "Dua", "Tiga", "Empat"]'],
    'too long' => [json_encode([str_repeat('a', 481), 'Dua', 'Tiga', 'Empat'])],
    'no room for link' => [json_encode(['Satu', 'Dua', 'Tiga', str_repeat('a', 480)])],
    'malformed' => ['not JSON'],
]);

it('accepts fenced JSON without truncating the final paragraph', function () {
    CariKaryawanAgent::fake(["```json\n[\"Satu\",\"Dua\",\"Tiga\",\"Penutup utuh.\"]\n```"])->preventStrayPrompts();
    $item = threadsChainItem();

    publishThreadsChainItem($item);

    expect($this->containers)->toHaveCount(4)
        ->and($this->containers[3]['text'])->toStartWith("Penutup utuh.\n\n")
        ->and($this->containers[3]['text'])->toContain($item->url());
});

it('sends a fallback link to the last successful reply when a middle reply fails', function () {
    CariKaryawanAgent::fake(['["Satu", "Dua", "Tiga", "Empat"]'])->preventStrayPrompts();
    $this->responses = Http::sequence()
        ->push(['id' => 'c1'])->push(['id' => 'p1'])
        ->push(['id' => 'c2'])->push(['id' => 'p2'])
        ->push(['error' => ['message' => 'Reply rejected']], 400)
        ->push(['id' => 'fallback'])->push(['id' => 'fallback-published']);
    $item = threadsChainItem();

    expect(publishThreadsChainItem($item)['id'])->toBe('p1');

    Http::assertSent(fn ($request) => ($request['reply_to_id'] ?? null) === 'p2' && str_contains($request['text'] ?? '', $item->url()));
    expect($item->fresh()->posted_to_threads_at)->not->toBeNull();
    Http::assertSentCount(7);
});

it('does not fail the main post if both a middle reply and fallback link fail', function () {
    CariKaryawanAgent::fake(['["Satu", "Dua", "Tiga", "Empat"]'])->preventStrayPrompts();
    $this->responses = Http::sequence()->push(['id' => 'c1'])->push(['id' => 'p1'])
        ->push(['error' => ['message' => 'Rejected']], 400)
        ->push(['error' => ['message' => 'Rejected']], 400);
    $item = threadsChainItem();

    expect(publishThreadsChainItem($item)['id'])->toBe('p1');
    Http::assertSentCount(4);
});

it('does not retry an ambiguous final link publish failure', function () {
    CariKaryawanAgent::fake(['["Satu", "Dua", "Tiga", "Empat"]'])->preventStrayPrompts();
    $this->responses = Http::sequence()->push(['id' => 'c1'])->push(['id' => 'p1'])
        ->push(['id' => 'c2'])->push(['id' => 'p2'])
        ->push(['id' => 'c3'])->push(['id' => 'p3'])
        ->push(['id' => 'c4'])->push([], 200);
    $item = threadsChainItem();

    expect(publishThreadsChainItem($item)['id'])->toBe('p1');
    Http::assertSentCount(8);
});

it('does not mark a post shared if the first container or publish has no valid ID', function (array $responses) {
    CariKaryawanAgent::fake(['["Satu", "Dua", "Tiga", "Empat"]'])->preventStrayPrompts();
    $sequence = $this->responses = Http::sequence();
    foreach ($responses as $response) {
        $sequence->push($response);
    }
    $item = threadsChainItem();

    expect(fn () => publishThreadsChainItem($item))->toThrow(Exception::class);
    expect($item->fresh()->posted_to_threads_at)->toBeNull();
    Http::assertSentCount(count($responses));
})->with([
    'missing container ID' => [[[]]],
    'missing publish ID' => [[['id' => 'c1'], []]],
    'invalid publish ID' => [[['id' => 'c1'], ['id' => []]]],
]);

it('saves chain settings in Filament and validates whole-number lengths', function () {
    $this->actingAs(User::factory()->create());
    Filament::setCurrentPanel(Filament::getPanel('rahasia'));
    CariKaryawanAgent::fake()->preventStrayPrompts();

    $page = Livewire::test(ManageThreadsSettings::class)
        ->assertSet('data.threads_chain_posts', 4)
        ->set('data.threads_chain_posts', 7)
        ->call('save')
        ->assertHasErrors(['data.threads_chain_posts']);
    $page->set('data.threads_chain_posts', 4.5)->call('save')
        ->assertHasErrors(['data.threads_chain_posts']);
    $page->set('data.threads_chain_posts', 6)->call('save')->assertHasNoErrors();
    expect(Setting::get('threads_chain_posts'))->toBe('6');
    $page->set('data.threads_chain_mode_enabled', false)->call('save')->assertHasNoErrors();
    expect((bool) Setting::get('threads_chain_mode_enabled'))->toBeFalse();
    Http::assertNothingSent();
    CariKaryawanAgent::assertNeverPrompted();
});

it('defaults chain mode to off when settings are absent', function () {
    Setting::whereIn('key', ['threads_chain_mode_enabled', 'threads_chain_posts'])->delete();
    \Illuminate\Support\Facades\Cache::flush();
    CariKaryawanAgent::fake(['Teks utama biasa.'])->preventStrayPrompts();

    publishThreadsChainItem(threadsChainItem());

    expect($this->containers)->toHaveCount(2);
    CariKaryawanAgent::assertPromptedTimes(1);
});

it('clamps numeric chain lengths and defaults malformed values to four', function (string $value, int $count) {
    Setting::set('threads_chain_posts', $value);
    CariKaryawanAgent::fake([json_encode(array_fill(0, $count, 'Cerita tersambung.'))])->preventStrayPrompts();

    publishThreadsChainItem(threadsChainItem());

    expect($this->containers)->toHaveCount($count);
})->with([['1', 3], ['10', 6], ['oops', 4], ['4.5', 4]]);
