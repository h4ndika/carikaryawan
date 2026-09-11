<div class="min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Category -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ $post->categoryUrl() }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">
                {{ $post->category->name }}
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ $post->categoryWithCityUrl() }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">{{ $post->city->name }}</a>
        </nav>

        <!-- Article Header -->
        <header class="w-full mb-8">
            <div class="max-w-4xl space-y-4 mb-6">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="px-2.5 py-1 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60 text-xs font-bold uppercase tracking-wider">
                        {{ $post->category->name }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        <i class="fa-solid fa-location-dot text-amber-800 dark:text-amber-400 text-[11px]"></i>
                        {{ $post->city->name }}@if($post->province), {{ $post->province->name }}@endif
                    </span>
                </div>

                <h1 class="font-serif-title text-3xl sm:text-4xl md:text-5xl font-bold leading-tight text-zinc-950 dark:text-zinc-50 tracking-tight">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 font-normal leading-relaxed">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </div>

            <!-- Author / Curator Metadata & Share (Full Width matching Image) -->
            <div class="w-full pt-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-4 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        D
                    </div>
                    <div>
                        <span class="text-zinc-900 dark:text-zinc-200 font-bold block">Kurasi Redaksi DISEKITAR</span>
                        <span>{{ $post->published_at ? \Illuminate\Support\Carbon::parse($post->published_at)->translatedFormat('d F Y') : date('d F Y') }} · {{ max(1, round(str_word_count(strip_tags($post->content ?? '')) / 200)) }} min baca</span>
                    </div>
                </div>

                <!-- Action & Share Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    @if (!$post->isJob())
                        <x-story-card-modal :post="$post" />
                        <x-embed-widget-modal :post="$post" />
                    @endif

                    <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800 mx-1 hidden sm:block"></div>

                    <span class="text-zinc-400 dark:text-zinc-500 text-[11px] uppercase tracking-wider font-semibold">Bagikan:</span>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url()->current()) }}" 
                       target="_blank" 
                       class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-colors"
                       title="WhatsApp">
                        <i class="fa-brands fa-whatsapp text-sm"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" 
                       target="_blank" 
                       class="w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 flex items-center justify-center transition-colors"
                       title="X / Twitter">
                        <i class="fa-brands fa-x-twitter text-sm"></i>
                    </a>
                </div>
            </div>

            @if (!$post->isJob() && ($post->city || $post->latitude))
                <x-weather-aqi-widget :lat="$post->latitude ?? $post->city?->latitude" 
                                      :lon="$post->longitude ?? $post->city?->longitude" 
                                      :location-name="$post->city?->name ?? 'Sekitar'" />
            @endif
        </header>

        @php
            $mainImg = $post->primary_image_url;
            $fallbackGalleryUrls = $post->fallback_image_urls;
            $mainSource = $post->primary_image_source;
        @endphp

        @if ($post->isJob())
            <!-- Dynamic Job Cover Visual (1200x630 format) -->
            <div class="mb-6">
                <figure class="relative rounded-2xl overflow-hidden shadow-xs border border-zinc-200/80 dark:border-zinc-800 bg-zinc-950">
                    <img src="{{ route('job.image', ['slug' => $post->slug]) }}" 
                         alt="{{ $post->title }}" 
                         class="w-full h-auto object-cover max-h-[460px]"
                         loading="eager"
                         fetchpriority="high">
                </figure>
            </div>
        @else
            <!-- Featured Image with Placeholder Fallback & Gallery Fallback -->
            <div class="mb-4 sm:mb-6">
                <figure class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-800">
                    @if ($mainImg)
                        <button type="button" 
                                wire:click="openLightbox(@js($mainImg))"
                                class="block w-full cursor-zoom-in group">
                            <img src="{{ $mainImg }}" 
                                 alt="{{ $post->title }}" 
                                 referrerpolicy="no-referrer"
                                 data-fallbacks='@json($fallbackGalleryUrls)'
                                 onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                 class="w-full max-h-[540px] object-cover transition duration-700 group-hover:scale-[1.01]"
                                 loading="eager"
                                 fetchpriority="high"
                                 decoding="async">
                            <div class="absolute bottom-4 right-4 flex items-center gap-2">
                                @if ($mainSource)
                                    <span class="bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium opacity-90">
                                        <i class="fa-solid fa-camera mr-1 text-[10px]"></i> Sumber: {{ $mainSource }}
                                    </span>
                                @endif
                                <span class="bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium opacity-90 group-hover:opacity-100 transition-opacity">
                                    <i class="fa-solid fa-expand mr-1.5"></i> Perbesar Foto
                                </span>
                            </div>
                        </button>
                    @else
                        <div class="w-full h-72 sm:h-96 bg-zinc-100 dark:bg-zinc-800 flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500 p-8">
                            <i class="fa-solid fa-camera text-5xl text-zinc-300 dark:text-zinc-600 mb-3"></i>
                            <span class="text-xs uppercase font-bold tracking-widest text-zinc-400 dark:text-zinc-500">DOKUMENTASI KURASI DISEKITAR</span>
                        </div>
                    @endif
                </figure>
            </div>
        @endif

        <!-- Main 2-Column Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- Left Column: Story Prose & Gallery (8 cols) -->
            <div class="lg:col-span-8">
                @if ($post->isJob() && $post->isExpired())
                    <div class="mb-6 p-4 sm:p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30 dark:border-amber-500/20 text-amber-950 dark:text-amber-200 flex items-start gap-3.5 shadow-xs">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-700 dark:text-amber-400 shrink-0 text-lg mt-0.5">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm sm:text-base text-amber-950 dark:text-amber-100">Lowongan Kerja Ini Telah Ditutup</h3>
                                <span class="bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Ditutup</span>
                            </div>
                            <p class="text-xs sm:text-sm mt-1 text-amber-900/80 dark:text-amber-200/80 leading-relaxed">
                                Masa aktif publikasi lowongan kerja ini (21 hari) telah berakhir{{ $post->expired_at ? ' pada ' . $post->expired_at->translatedFormat('d F Y') : '' }}. Pendaftaran dan tautan lamaran telah dinonaktifkan. Anda dapat melihat rekomendasi lowongan kerja serupa yang masih aktif di bagian bawah halaman ini.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Article Prose -->
                <article class="prose-editorial max-w-none bg-white dark:bg-zinc-900 px-6 sm:px-10 pt-4 sm:pt-6 pb-8 sm:pb-10 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                    {!! $post->content !!}
                </article>

                <!-- Ad Slot: Di Atas Galeri -->
                <x-ad-slot slotName="ad_post_before_gallery" />

                <!-- Photo Gallery Grid (if available) -->
                @if (!$post->isJob() && $post->images && $post->images->isNotEmpty())
                    <section class="mt-8 bg-white dark:bg-zinc-900 p-6 sm:p-8 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="mb-6 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h2 class="font-serif-title text-xl font-bold text-zinc-950 dark:text-zinc-50">Galeri & Suasana</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($post->images as $img)
                                @php
                                    $imgSource = \App\Models\Post::extractImageSourceDomain($img->image_url);
                                @endphp
                                <figure data-hide-on-error class="gallery-item group relative rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-800">
                                    <button type="button" 
                                            wire:click="openLightbox(@js($img->image_url))"
                                            class="block w-full aspect-[4/3] cursor-zoom-in relative">
                                        <img src="{{ $img->image_url }}" 
                                             alt="{{ $img->caption ?: $post->title }}" 
                                             referrerpolicy="no-referrer"
                                             data-hide-on-error="true"
                                             onerror="const f=this.closest('figure'); if(f){ const p=f.parentElement; f.remove(); if(p && p.querySelectorAll('figure').length===0){ p.closest('section')?.remove(); } }"
                                             class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                             loading="lazy">
                                        @if ($imgSource)
                                            <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/60 backdrop-blur-xs text-[9px] font-medium text-white/90 tracking-wide pointer-events-none">
                                                Foto: {{ $imgSource }}
                                            </span>
                                        @endif
                                    </button>
                                    @if ($img->caption)
                                        <figcaption class="p-2.5 text-xs text-zinc-600 dark:text-zinc-400 bg-white dark:bg-zinc-900 border-t border-zinc-100 dark:border-zinc-800">
                                            {{ $img->caption }}
                                        </figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>

            <!-- Right Column: Location Card & Place Details (4 cols) -->
            <aside class="lg:col-span-4 space-y-8 sticky top-24">
                
                <!-- Ad Slot: Sidebar Atas Detail Post -->
                <x-ad-slot slotName="ad_post_sidebar_top" class="!mt-0 !mb-6" />

                <!-- Place Summary Card -->
                @php
                    $applyUrl = $post->website ?: ($post->details?->first(fn($d) => in_array(strtolower(trim($d->meta_key)), ['tautan_lamaran', 'link_lamaran', 'url_lamaran', 'link_pendaftaran', 'url_lowongan', 'website', 'url', 'link']))?->meta_value);
                @endphp
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800 dark:text-amber-400 block mb-1">
                            {{ $post->isJob() ? 'Informasi Lowongan' : 'Informasi Tempat' }}
                        </span>
                        <h3 class="font-serif-title font-bold text-xl text-zinc-950 dark:text-zinc-50">
                            {{ $post->title }}
                        </h3>

                        @if ($post->rating)
                            <div class="flex items-center gap-2 mt-2.5">
                                <div class="flex items-center text-amber-500 text-xs gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                        @if ($i <= round($post->rating))
                                            <i class="fa-solid fa-star"></i>
                                        @else
                                            <i class="fa-regular fa-star text-zinc-300 dark:text-zinc-700"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($post->rating, 1) }}</span>
                                @if ($post->reviews_count)
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">({{ number_format($post->reviews_count) }} ulasan Google)</span>
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($post->isJob() && $applyUrl)
                        <div>
                            @if ($post->isExpired())
                                <button disabled class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 text-xs font-semibold tracking-wide cursor-not-allowed border border-zinc-200 dark:border-zinc-700/60">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                    <span>Pendaftaran Telah Ditutup</span>
                                </button>
                            @else
                                <a href="{{ $applyUrl }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 text-xs font-semibold tracking-wide transition-colors shadow-xs">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Lamar Sekarang</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-70"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Place Info Attributes -->
                    <div class="space-y-5 text-xs overflow-hidden">
                        @if ($post->formatted_address)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Alamat</span>
                                    <span class="text-zinc-800 dark:text-zinc-200 font-medium leading-relaxed block break-words">{{ $post->formatted_address }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($post->phone)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Kontak & Telepon</span>
                                    <a href="tel:{{ $post->phone }}" class="text-zinc-900 dark:text-zinc-100 font-semibold hover:text-amber-800 dark:hover:text-amber-400 transition-colors break-words">
                                        {{ $post->phone }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if ($post->website && !$post->isJob())
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-globe text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Situs Resmi</span>
                                    <a href="{{ $post->website }}" target="_blank" rel="noopener noreferrer" class="text-amber-800 dark:text-amber-400 font-semibold hover:underline inline-flex items-center gap-1 break-all">
                                        Kunjungi Website <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Key Value Details with Clean Humanized Labels & Icons -->
                        @if ($post->details && $post->details->isNotEmpty())
                            @php
                                $detailMetaMap = [
                                    'posisi' => ['label' => 'Posisi', 'icon' => 'fa-solid fa-briefcase'],
                                    'nama_perusahaan' => ['label' => 'Nama Perusahaan', 'icon' => 'fa-solid fa-building'],
                                    'perusahaan' => ['label' => 'Nama Perusahaan', 'icon' => 'fa-solid fa-building'],
                                    'gaji' => ['label' => 'Gaji', 'icon' => 'fa-solid fa-money-bill-wave'],
                                    'kualifikasi' => ['label' => 'Kualifikasi', 'icon' => 'fa-solid fa-user-check'],
                                    'deskripsi_pekerjaan' => ['label' => 'Deskripsi Pekerjaan', 'icon' => 'fa-solid fa-clipboard-list'],
                                    'tipe_pekerjaan' => ['label' => 'Tipe Pekerjaan', 'icon' => 'fa-solid fa-business-time'],
                                    'jam_buka' => ['label' => 'Jam Operasional', 'icon' => 'fa-regular fa-clock'],
                                    'jam_operasional' => ['label' => 'Jam Operasional', 'icon' => 'fa-regular fa-clock'],
                                    'opening_hours' => ['label' => 'Jam Operasional', 'icon' => 'fa-regular fa-clock'],
                                    'harga' => ['label' => 'Estimasi Harga', 'icon' => 'fa-solid fa-tag'],
                                    'rentang_harga' => ['label' => 'Rentang Harga', 'icon' => 'fa-solid fa-tag'],
                                    'price' => ['label' => 'Tarif / Harga', 'icon' => 'fa-solid fa-tag'],
                                    'fasilitas' => ['label' => 'Fasilitas Utama', 'icon' => 'fa-solid fa-concierge-bell'],
                                    'facilities' => ['label' => 'Fasilitas', 'icon' => 'fa-solid fa-concierge-bell'],
                                    'lokasi' => ['label' => 'Lokasi / Alamat', 'icon' => 'fa-solid fa-map-pin'],
                                    'alamat' => ['label' => 'Alamat', 'icon' => 'fa-solid fa-map-pin'],
                                    'tiket_masuk' => ['label' => 'Tiket Masuk', 'icon' => 'fa-solid fa-ticket'],
                                    'menu_favorit' => ['label' => 'Menu Favorit', 'icon' => 'fa-solid fa-utensils'],
                                    'rekomendasi' => ['label' => 'Rekomendasi', 'icon' => 'fa-solid fa-thumbs-up'],
                                    'website' => ['label' => 'Website', 'icon' => 'fa-solid fa-globe'],
                                    'telepon' => ['label' => 'Telepon', 'icon' => 'fa-solid fa-phone'],
                                    'kontak' => ['label' => 'Kontak', 'icon' => 'fa-solid fa-phone'],
                                ];
                            @endphp

                            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 space-y-4">
                                @foreach ($post->details as $detail)
                                    @php
                                        $normKey = strtolower(trim($detail->meta_key));
                                        $normVal = trim($detail->meta_value);

                                        // Skip duplicate attributes if already shown in primary card fields
                                        if (in_array($normKey, ['lokasi', 'alamat']) && !empty($post->formatted_address)) {
                                            continue;
                                        }
                                        // ponytail: skips redundant job application link details since it is already hoisted as the primary CTA button
                                        if ($post->isJob() && in_array($normKey, ['tautan_lamaran', 'link_lamaran', 'url_lamaran', 'link_pendaftaran', 'url_lowongan', 'tautan', 'link', 'url', 'website', 'situs', 'situs_web'])) {
                                            continue;
                                        }
                                        if (!empty($applyUrl) && ($normVal === trim($applyUrl) || in_array($normKey, ['website', 'situs', 'situs_web', 'url', 'link', 'tautan', 'tautan_lamaran', 'link_lamaran', 'url_lamaran', 'link_pendaftaran', 'url_lowongan']))) {
                                            continue;
                                        }
                                        if (in_array($normKey, ['telepon', 'phone', 'kontak']) && !empty($post->phone)) {
                                            continue;
                                        }
                                        if (in_array($normKey, ['nama', 'name', 'judul', 'title']) && strtolower(trim($detail->meta_value)) === strtolower(trim($post->title))) {
                                            continue;
                                        }
                                        if (in_array($normKey, ['kategori', 'category']) && $post->category && strtolower(trim($detail->meta_value)) === strtolower(trim($post->category->name))) {
                                            continue;
                                        }

                                        $metaConfig = $detailMetaMap[$normKey] ?? [
                                            'label' => ucwords(str_replace(['_', '-'], ' ', $detail->meta_key)),
                                            'icon' => 'fa-solid fa-circle-info'
                                        ];

                                        $isUrl = str_starts_with($detail->meta_value, 'http://') || str_starts_with($detail->meta_value, 'https://');
                                    @endphp

                                    <div class="flex items-start gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-amber-800 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                            <i class="{{ $metaConfig['icon'] }} text-xs"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">
                                                {{ $metaConfig['label'] }}
                                            </span>
                                            @if ($normKey === 'fasilitas' || $normKey === 'facilities')
                                                @php
                                                    $facilities = array_filter(array_map('trim', explode(',', $detail->meta_value)));
                                                @endphp
                                                @if (count($facilities) > 1)
                                                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                        @foreach ($facilities as $facility)
                                                             <span class="inline-block px-2.5 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 font-medium text-[11px] break-words">
                                                                {{ $facility }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-zinc-800 dark:text-zinc-200 font-medium leading-relaxed block break-words">{{ $detail->meta_value }}</span>
                                                @endif
                                            @elseif ($isUrl)
                                                <a href="{{ $detail->meta_value }}" target="_blank" rel="noopener noreferrer" class="text-amber-800 dark:text-amber-400 font-semibold hover:underline inline-flex items-center gap-1 break-all">
                                                    Kunjungi Tautan <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                </a>
                                            @elseif (in_array($normKey, ['harga', 'rentang_harga', 'price', 'tiket_masuk']))
                                                <span class="text-zinc-950 dark:text-zinc-50 font-bold text-sm block break-words">{{ $detail->meta_value }}</span>
                                            @else
                                                <span class="text-zinc-800 dark:text-zinc-200 font-medium leading-relaxed block break-words">{{ $detail->meta_value }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Open in Maps Button (Hidden as requested) -->
                    {{--
                    @if ($post->place_id || ($post->latitude && $post->longitude) || $post->formatted_address)
                        @php
                            $mapsUrl = $post->place_id 
                                ? "https://www.google.com/maps/search/?api=1&query=" . urlencode($post->title) . "&query_place_id=" . $post->place_id
                                : "https://www.google.com/maps/search/?api=1&query=" . urlencode($post->title . ' ' . ($post->formatted_address ?? $post->city->name));
                        @endphp
                        <div class="pt-2">
                            <a href="{{ $mapsUrl }}" 
                               target="_blank" 
                               rel="noopener noreferrer"
                               class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-950 text-xs font-semibold tracking-wide transition-colors">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Petunjuk Arah di Google Maps
                            </a>
                        </div>
                    @endif
                    --}}

                    <!-- Affiliate Booking & Reservasi Buttons -->
                    @php
                        $affiliateLinks = [];
                        if (!empty($post->affiliate_links) && is_array($post->affiliate_links)) {
                            $affiliateLinks = array_values(array_filter($post->affiliate_links, function ($link) {
                                return !empty($link['url']) && ($link['is_active'] ?? true);
                            }));
                        }
                    @endphp

                    @if (!empty($affiliateLinks))
                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-check text-xs"></i>
                                    Booking & Reservasi
                                </span>
                                <span class="text-[10px] text-zinc-400 dark:text-zinc-500 font-medium">Partner Resmi</span>
                            </div>

                            <div class="space-y-2">
                                @foreach ($affiliateLinks as $affLink)
                                    @php
                                        $provider = strtolower($affLink['provider'] ?? 'custom');
                                        $label = $affLink['label'] ?? 'Pesan Sekarang';
                                        $url = $affLink['url'];
                                        $badge = $affLink['badge_text'] ?? null;

                                        // Provider-specific styling & brand icons
                                        $brandStyles = match($provider) {
                                            'traveloka' => [
                                                'bg' => 'bg-[#007CE8] hover:bg-[#0069C7] text-white',
                                                'icon' => 'fa-solid fa-plane-departure',
                                                'name' => 'Traveloka'
                                            ],
                                            'tiket_com' => [
                                                'bg' => 'bg-[#0064D2] hover:bg-[#0051A8] text-white',
                                                'icon' => 'fa-solid fa-ticket',
                                                'name' => 'Tiket.com'
                                            ],
                                            'agoda' => [
                                                'bg' => 'bg-[#5392F9] hover:bg-[#3D7EE8] text-white',
                                                'icon' => 'fa-solid fa-hotel',
                                                'name' => 'Agoda'
                                            ],
                                            'booking_com' => [
                                                'bg' => 'bg-[#003580] hover:bg-[#00275E] text-white',
                                                'icon' => 'fa-solid fa-bed',
                                                'name' => 'Booking.com'
                                            ],
                                            'trip_com' => [
                                                'bg' => 'bg-[#287DFA] hover:bg-[#1E67D1] text-white',
                                                'icon' => 'fa-solid fa-globe',
                                                'name' => 'Trip.com'
                                            ],
                                            'airbnb' => [
                                                'bg' => 'bg-[#FF385C] hover:bg-[#E00B41] text-white',
                                                'icon' => 'fa-solid fa-house-chimney-user',
                                                'name' => 'Airbnb'
                                            ],
                                            'pegipegi' => [
                                                'bg' => 'bg-[#FF7200] hover:bg-[#E56600] text-white',
                                                'icon' => 'fa-solid fa-suitcase-rolling',
                                                'name' => 'Pegipegi'
                                            ],
                                            default => [
                                                'bg' => 'bg-amber-700 hover:bg-amber-800 text-white',
                                                'icon' => 'fa-solid fa-arrow-up-right-from-square',
                                                'name' => 'Pesan'
                                            ],
                                        };
                                    @endphp

                                    <a href="{{ $url }}" 
                                       target="_blank" 
                                       rel="nofollow sponsored noopener noreferrer"
                                       class="w-full flex items-center justify-between py-2.5 px-4 rounded-xl {{ $brandStyles['bg'] }} text-xs font-semibold tracking-wide transition-all shadow-xs hover:shadow-md cursor-pointer group">
                                        <span class="flex items-center gap-2.5">
                                            <i class="{{ $brandStyles['icon'] }} text-xs opacity-90 group-hover:scale-110 transition-transform"></i>
                                            <span>{{ $label }}</span>
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            @if ($badge)
                                                <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full backdrop-blur-xs">
                                                    {{ $badge }}
                                                </span>
                                            @endif
                                            <i class="fa-solid fa-arrow-right text-[10px] opacity-80 group-hover:translate-x-0.5 transition-transform"></i>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Back to City Guide -->
                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">Mencari referensi lain di {{ $post->city->name }}?</p>
                    <a href="{{ route('search', ['city' => $post->city->slug]) }}" 
                       wire:navigate
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 transition-colors">
                        Lihat Semua Tempat di {{ $post->city->name }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </aside>
        </div>

        <!-- Proximity Cross-Category Linking (Tempat Menarik Terdekat) -->
        @if (isset($nearbyOtherCategories) && $nearbyOtherCategories->isNotEmpty())
            <section class="mt-16 pt-12 border-t border-zinc-200 dark:border-zinc-800">
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400">
                            Di Sekitar Sini
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-900 dark:text-amber-300">
                            Radius 5 km
                        </span>
                    </div>
                    <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                        Tempat Menarik Terdekat dari Kategori Lain
                    </h2>
                    <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        Rekomendasi tempat populer di dekat {{ $post->title }} untuk melengkapi kunjungan Anda.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($nearbyOtherCategories as $nearby)
                        @php
                            $nearbyThumb = $nearby->primary_image_url;
                            $nearbyDist = isset($nearby->distance_km) ? round((float) $nearby->distance_km, 2) : null;
                        @endphp
                        <article class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 overflow-hidden shadow-xs hover:shadow-md transition-all duration-300 flex flex-col">
                            <a href="{{ $nearby->url() }}" wire:navigate class="block aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
                                @if ($nearbyThumb)
                                    <img src="{{ $nearbyThumb }}" 
                                         alt="{{ $nearby->title }}" 
                                         referrerpolicy="no-referrer"
                                         data-fallbacks='@json($nearby->fallback_image_urls ?? [])'
                                         onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500">
                                        <i class="fa-solid fa-image text-2xl mb-1 text-zinc-300 dark:text-zinc-600"></i>
                                    </div>
                                @endif

                                @if ($nearbyDist !== null)
                                    <div class="absolute top-2 right-2 px-2 py-0.5 rounded-md bg-zinc-950/80 backdrop-blur-xs text-white text-[10px] font-bold flex items-center gap-1 shadow-xs pointer-events-none">
                                        <i class="fa-solid fa-location-arrow text-amber-400 text-[9px]"></i>
                                        <span>{{ $nearbyDist < 1 ? round($nearbyDist * 1000) . ' m' : $nearbyDist . ' km' }}</span>
                                    </div>
                                @endif

                                @if ($nearby->category)
                                    <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-white/90 dark:bg-zinc-900/90 backdrop-blur-xs text-zinc-900 dark:text-zinc-100 text-[9px] font-bold uppercase tracking-wider pointer-events-none">
                                        {{ $nearby->category->name }}
                                    </div>
                                @endif
                            </a>

                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-serif-title font-bold text-sm text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2 leading-snug">
                                        <a href="{{ $nearby->url() }}" wire:navigate>
                                            {{ $nearby->title }}
                                        </a>
                                    </h3>
                                </div>
                                <div class="mt-3 pt-2 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-zinc-500 dark:text-zinc-400">
                                    <span class="truncate">{{ $nearby->city->name ?? 'Indonesia' }}</span>
                                    <span class="font-semibold text-amber-800 dark:text-amber-400 flex items-center gap-1">
                                        Lihat <i class="fa-solid fa-arrow-right text-[9px]"></i>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Related Stories / Jobs in Same City -->
        @if (isset($related) && $related->isNotEmpty())
            <section class="mt-20 pt-12 border-t border-zinc-200 dark:border-zinc-800">
                <div class="mb-8 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">Eksplorasi Lanjutan</span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                            {{ $post->isJob() ? 'Lowongan Kerja Terkait' : 'Cerita Terkait' }} di {{ $post->city?->name ?? 'Sekitar' }}
                        </h2>
                    </div>
                    @if ($post->city)
                        <a href="{{ route('search', ['city' => $post->city->slug]) }}" 
                           wire:navigate
                           class="text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 hidden sm:inline-flex items-center gap-1">
                            Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach ($related as $item)
                        @include('partials.post-card', ['post' => $item])
                    @endforeach
                </div>
            </section>
        @endif

    </div>

    <!-- Lightbox Modal -->
    @if ($lightbox)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-sm"
             wire:click="closeLightbox"
             x-data
             x-on:keydown.escape.window="$wire.closeLightbox()">
            <button type="button"
                    class="absolute right-6 top-6 rounded-full bg-white/10 hover:bg-white/20 px-4 py-2 text-xs font-semibold text-white transition cursor-pointer"
                    wire:click.stop="closeLightbox">
                Tutup ✕
            </button>
            <img src="{{ $lightbox }}"
                 alt="Preview Foto"
                 referrerpolicy="no-referrer"
                 onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                 class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain shadow-2xl"
                 wire:click.stop>
        </div>
    @endif
</div>