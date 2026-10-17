<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Post;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Http::preventStrayRequests();
    Http::fake();
    $this->travelTo(now()->startOfSecond());
    $this->province = Province::create(['slug' => 'provinsi-seo', 'name' => 'Provinsi SEO']);
    $this->city = City::create(['slug' => 'kota-seo', 'name' => 'Kota SEO', 'province_id' => $this->province->id]);
    $this->post = Post::create([
        'title' => 'Admin - PT Uji', 'slug' => 'admin-seo',
        'category_id' => Category::jobs()->firstOrFail()->id,
        'city_id' => $this->city->id, 'province_id' => $this->province->id,
        'status' => 'published', 'published_at' => now(),
    ]);
});

it('omits unknown street and postal data and uses an existing publisher logo', function () {
    $this->post->details()->createMany([
        ['meta_key' => 'lokasi', 'meta_value' => 'Kota SEO'],
        ['meta_key' => 'perusahaan', 'meta_value' => 'PT Uji'],
    ]);
    $graph = $this->post->generateJsonLd()['@graph'];
    $job = collect($graph)->firstWhere('@type', 'JobPosting');
    expect($job['title'])->toBe('Admin')
        ->and($job['jobLocation']['address'])->toBe([
            '@type' => 'PostalAddress', 'addressLocality' => 'Kota SEO',
            'addressRegion' => 'Provinsi SEO', 'addressCountry' => 'ID',
        ])
        ->and($graph[0]['publisher']['logo']['url'])->toBe(asset('android-chrome-512x512.png'));
    expect(is_file(public_path('android-chrome-512x512.png')))->toBeTrue();
    Http::assertNothingSent();
});

it('uses explicit position and actual address details', function () {
    $this->post->details()->createMany([
        ['meta_key' => 'posisi', 'meta_value' => 'Senior Admin'],
        ['meta_key' => 'alamat', 'meta_value' => 'Jalan Uji No. 12'],
        ['meta_key' => 'kode_pos', 'meta_value' => '12345'],
    ]);
    $job = collect($this->post->generateJsonLd()['@graph'])->firstWhere('@type', 'JobPosting');
    expect($job['title'])->toBe('Senior Admin')
        ->and($job['jobLocation']['address']['streetAddress'])->toBe('Jalan Uji No. 12')
        ->and($job['jobLocation']['address']['postalCode'])->toBe('12345');
});

it('does not fabricate a region or strip an unknown title suffix', function () {
    $this->post->title = 'Admin - Finance';
    $this->post->setRelation('city', null)->setRelation('province', null);
    $job = collect($this->post->generateJsonLd()['@graph'])->firstWhere('@type', 'JobPosting');
    expect($job['title'])->toBe('Admin - Finance')
        ->and($job['jobLocation']['address'])->toBe(['@type' => 'PostalAddress', 'addressCountry' => 'ID']);
});

it('keeps expiration and dedup rules intact', function () {
    $job = collect($this->post->generateJsonLd()['@graph'])->firstWhere('@type', 'JobPosting');
    expect($job['validThrough'])->toBe($this->post->published_at->copy()->addDays(21)->toIso8601String())
        ->and(Post::jobPlaceId('PT Uji', 'Admin', 'Bandung'))->toBe('job_' . substr(md5('uji_admin_bandung'), 0, 24));
    $this->post->expired_at = now()->subSecond();
    expect(collect($this->post->generateJsonLd()['@graph'])->contains('@type', 'JobPosting'))->toBeFalse()
        ->and($this->post->seoRobots())->toBe('noindex,follow');
});

it('redirects only the configured www alias while preserving raw path and query', function () {
    config(['app.url' => 'https://carikaryawan.test']);
    $this->get('http://www.carikaryawan.test/lowongan-kerja/kota-seo?next=https%3A%2F%2Fevil.test&x=one%20two')
        ->assertStatus(301)
        ->assertRedirect('https://carikaryawan.test/lowongan-kerja/kota-seo?next=https%3A%2F%2Fevil.test&x=one%20two');
    $this->get('http://www.carikaryawan.test//evil.test/path?x=1')
        ->assertStatus(301)->assertRedirect('https://carikaryawan.test//evil.test/path?x=1');
});

it('does not redirect apex localhost or unrelated hosts', function (string $host) {
    config(['app.url' => 'https://carikaryawan.test']);
    $this->get('http://' . $host . '/sitemap.xml')->assertOk();
})->with(['carikaryawan.test', 'localhost', 'www.other.test', 'www.carikaryawan.test.evil.test']);

it('uses page specific canonicals and crawlable pagination links', function (bool $withCity) {
    foreach (range(2, 21) as $number) {
        $this->post->replicate()->fill(['slug' => 'admin-seo-' . $number])->save();
    }
    $url = $withCity ? $this->post->categoryWithCityUrl() : $this->post->categoryUrl();
    $this->get($url . '?page=2&utm_source=test')->assertOk()
        ->assertSee('<link rel="canonical" href="' . $url . '?page=2">', false)
        ->assertSee('<a href="' . $url . '?page=1" rel="prev" wire:navigate', false)
        ->assertSee('<a href="' . $url . '?page=3" rel="next" wire:navigate', false)
        ->assertSee('aria-current="page"', false)
        ->assertDontSee('wire:click="gotoPage', false);
    $this->get($url . '?page=1&utm_source=test')->assertOk()
        ->assertSee('<link rel="canonical" href="' . $url . '">', false);
})->with([false, true]);

it('gives career tools clean canonicals and their own metadata', function (string $route, string $description) {
    $url = route($route);
    $this->get($url . '?job=' . $this->post->id . '&utm_source=test')->assertOk()
        ->assertSee('<link rel="canonical" href="' . $url . '">', false)
        ->assertSee('<meta name="description" content="' . $description . '">', false)
        ->assertSee('<meta property="og:url" content="' . $url . '">', false);
})->with([
    ['tools.hub', 'Kumpulan tools dan resources untuk membantu perjalanan karir Anda: CV Builder, Interview Prep, dan lainnya.'],
    ['career.cv-builder', 'Buat CV ATS-friendly yang rapi, profesional, dan siap cetak. Lengkapi data diri, pengalaman, pendidikan, serta keahlian dengan panduan analisis real-time.'],
    ['career.interview-prep', 'Latih wawancara kerja dengan AI. Dapatkan pertanyaan general, behavioral, dan teknis yang disesuaikan dengan posisi target.'],
]);

it('keeps sitemap timestamps stable and uses the latest listing modification', function () {
    $this->post->update(['updated_at' => now()->subDays(2)]);
    $latest = $this->post->replicate()->fill([
        'slug' => 'recently-edited-seo', 'published_at' => now()->subDays(5), 'updated_at' => now()->subHour(),
    ]);
    $latest->save();
    $this->post->replicate()->fill(['slug' => 'third-seo', 'updated_at' => now()->subDays(2)])->save();

    $first = $this->get('/sitemap.xml')->assertOk()->getContent();
    $this->travel(1)->hours();
    expect($this->get('/sitemap.xml')->assertOk()->getContent())->toBe($first);
    $urls = [];
    foreach (simplexml_load_string($first)->url as $node) {
        $urls[(string) $node->loc] = (string) $node->lastmod;
    }
    foreach (['home', 'page.about', 'page.privacy', 'page.terms', 'page.contact', 'page.disclaimer', 'tools.hub', 'career.cv-builder', 'career.interview-prep'] as $route) {
        expect($urls[route($route)])->toBe('');
    }
    expect($urls[$this->post->categoryWithCityUrl()])->toBe($latest->updated_at->toAtomString())
        ->and($urls[$latest->url()])->toBe($latest->updated_at->toAtomString());
});
