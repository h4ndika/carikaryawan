<div>
    @php
        $shortcutCities = \App\Models\City::navigation();
        $jobSlug = $categories->first()?->slug ?? 'lowongan-kerja';
    @endphp
    <section class="bg-zinc-100 dark:bg-zinc-900/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight max-w-2xl">Cari kerja, mulai di sini.</h1>
            <p class="mt-4 text-base sm:text-lg text-zinc-600 dark:text-zinc-300 max-w-2xl">Pilih posisi dan kota tujuanmu. Temukan lowongan, lalu lamar langsung ke sumber aslinya.</p>
            <form action="{{ route('search') }}" method="GET" class="mt-8 grid gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,0.7fr)_auto] items-end max-w-4xl">
                <div>
                    <label for="home-keyword" class="block text-sm font-semibold mb-2">Posisi atau kata kunci</label>
                    <input id="home-keyword" name="q" type="search" placeholder="Contoh: admin, barista, desainer" class="w-full rounded-lg bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 px-4 py-3 text-base">
                </div>
                <div>
                    <label for="home-city" class="block text-sm font-semibold mb-2">Kota</label>
                    <select id="home-city" name="city" class="w-full rounded-lg bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 px-4 py-3 text-base">
                        <option value="">Semua kota</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->slug }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-brand hover:bg-blue-800 text-white px-6 py-3 text-base font-bold cursor-pointer transition-colors">Cari lowongan</button>
            </form>
            @if ($shortcutCities->isNotEmpty())
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm">
                    <span class="text-zinc-600 dark:text-zinc-400">Langsung ke kota:</span>
                    @foreach ($shortcutCities as $city)
                        <a href="{{ route('posts.list', ['category' => $jobSlug, 'city' => $city->slug]) }}" wire:navigate class="font-semibold hover:underline">{{ $city->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
        <x-ad-slot slotName="ad_home_hero" class="!mt-0" />
        <div class="grid gap-10 lg:grid-cols-12 items-start">
            <section class="lg:col-span-8">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight">Lowongan terbaru</h2>
                    <a href="{{ route('search') }}" wire:navigate class="text-sm font-semibold hover:underline">Lihat semua</a>
                </div>
                <div class="space-y-3">
                    @forelse ($posts as $post)
                        @include('partials.article-feed', ['post' => $post])
                    @empty
                        <div class="rounded-xl bg-white dark:bg-zinc-900 p-8 sm:p-10">
                            <h3 class="text-lg font-bold">Belum ada lowongan</h3>
                            <p class="mt-2 max-w-prose text-zinc-600 dark:text-zinc-300">Lowongan akan tampil di sini setelah dipublikasikan. Sambil menunggu, siapkan dokumen lamaranmu.</p>
                            <a href="{{ route('tools.hub') }}" wire:navigate class="inline-block mt-5 font-semibold text-blue-700 dark:text-blue-400 hover:underline">Buka tools karier</a>
                        </div>
                    @endforelse
                </div>
            </section>
            <aside class="lg:col-span-4 space-y-8">
                <section class="bg-zinc-900 dark:bg-zinc-800 text-white rounded-xl p-6 sm:p-8">
                    <h2 class="text-xl font-bold">Siapkan lamaranmu.</h2>
                    <p class="mt-3 text-base leading-relaxed text-zinc-300">Buat CV, tulis surat lamaran, dan latihan interview sebelum mengirim lamaran.</p>
                    <ul class="mt-6 space-y-4 text-base font-semibold">
                        <li><a href="{{ route('career.cv-builder') }}" wire:navigate class="hover:underline">Buat CV</a></li>
                        <li><a href="{{ route('tools.hub') }}" wire:navigate class="hover:underline">Surat lamaran & tools lainnya</a></li>
                        <li><a href="{{ route('career.interview-prep') }}" wire:navigate class="hover:underline">Latihan interview</a></li>
                    </ul>
                </section>
                <section class="px-1">
                    <h2 class="text-base font-bold">Kenali sumber lowongan</h2>
                    <p class="mt-3 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">CariKaryawan adalah agregator, bukan pemberi kerja. Periksa detail dan keaslian lowongan di situs sumber sebelum melamar.</p>
                </section>
                <x-ad-slot slotName="ad_home_sidebar" />
            </aside>
        </div>
    </div>
</div>
