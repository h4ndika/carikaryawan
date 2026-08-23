<div class="min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Category -->
        <nav class="flex items-center gap-2 text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400 font-medium mb-6">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">Beranda</a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <a href="{{ route('home', ['category' => $post->category->slug]) }}" wire:navigate class="hover:text-zinc-950 dark:hover:text-zinc-100 transition-colors">
                {{ $post->category->name }}
            </a>
            <span class="text-zinc-300 dark:text-zinc-700">/</span>
            <span class="text-zinc-900 dark:text-zinc-200 font-semibold">{{ $post->city->name }}</span>
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

                <!-- Share Buttons -->
                <div class="flex items-center gap-2">
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
        </header>

        <!-- Featured Image with Placeholder Fallback & Gallery Fallback -->
        @php
            $mainImg = $post->primary_image_url;
            $fallbackGalleryUrls = $post->fallback_image_urls;
        @endphp
        <div class="mb-12">
            <figure class="relative rounded-2xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-800">
                @if ($mainImg)
                    <button type="button" 
                            wire:click="openLightbox(@js($mainImg))"
                            class="block w-full cursor-zoom-in group">
                        <img src="{{ $mainImg }}" 
                             alt="{{ $post->title }}" 
                             data-fallbacks='@json($fallbackGalleryUrls)'
                             onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                             class="w-full max-h-[540px] object-cover transition duration-700 group-hover:scale-[1.01]">
                        <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-xs font-medium opacity-90 group-hover:opacity-100 transition-opacity">
                            <i class="fa-solid fa-expand mr-1.5"></i> Perbesar Foto
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

        <!-- Main 2-Column Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            
            <!-- Left Column: Story Prose & Gallery (8 cols) -->
            <div class="lg:col-span-8">
                <!-- Article Prose -->
                <article class="prose-editorial max-w-none bg-white dark:bg-zinc-900 p-6 sm:p-10 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                    {!! $post->content !!}
                </article>

                <!-- Photo Gallery Grid (if available) -->
                @if ($post->images && $post->images->isNotEmpty())
                    <section class="mt-12 bg-white dark:bg-zinc-900 p-6 sm:p-8 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                        <div class="flex items-center justify-between mb-6 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                            <h2 class="font-serif-title text-xl font-bold text-zinc-950 dark:text-zinc-50">Galeri & Suasana</h2>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $post->images->count() }} Foto</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach ($post->images as $img)
                                <figure class="group relative rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-800">
                                    <button type="button" 
                                            wire:click="openLightbox(@js($img->image_url))"
                                            class="block w-full aspect-[4/3] cursor-zoom-in">
                                        <img src="{{ $img->image_url }}" 
                                             alt="{{ $img->caption ?: $post->title }}" 
                                             onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                                             class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                             loading="lazy">
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
                
                <!-- Place Summary Card -->
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-[10px] uppercase font-bold tracking-widest text-amber-800 dark:text-amber-400 block mb-1">
                            Informasi Tempat
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

                        @if ($post->website)
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
                                        // Skip duplicate attributes if already shown in primary card fields
                                        if (in_array($normKey, ['lokasi', 'alamat']) && !empty($post->formatted_address)) {
                                            continue;
                                        }
                                        if (in_array($normKey, ['website', 'situs', 'situs_web', 'url', 'link']) && !empty($post->website)) {
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

                    <!-- Open in Maps Button -->
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

        <!-- Related Stories in Same City -->
        @if (isset($related) && $related->isNotEmpty())
            <section class="mt-20 pt-12 border-t border-zinc-200 dark:border-zinc-800">
                <div class="mb-8 flex items-baseline justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-widest font-bold text-amber-800 dark:text-amber-400 block mb-1">Eksplorasi Lanjutan</span>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                            Cerita Terkait di {{ $post->city->name }}
                        </h2>
                    </div>
                    <a href="{{ route('search', ['city' => $post->city->slug]) }}" 
                       wire:navigate
                       class="text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-amber-800 dark:hover:text-amber-400 hidden sm:inline-flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($related as $item)
                        @php
                            $itemThumb = $item->primary_image_url;
                            $fallbackItemUrls = $item->fallback_image_urls;
                        @endphp
                        <article class="group space-y-3">
                            <a href="{{ route('posts.show', ['category' => $item->category->slug, 'city' => $item->city->slug, 'slug' => $item->slug]) }}" 
                               wire:navigate class="block aspect-[16/10] rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/60 dark:border-zinc-800">
                                @if ($itemThumb)
                                    <img src="{{ $itemThumb }}" 
                                         alt="{{ $item->title }}" 
                                         data-fallbacks='@json($fallbackItemUrls)'
                                         onerror="if(window.handleImageFallback){handleImageFallback(this);}else{this.onerror=null;this.src='/images/placeholder.svg';}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                         loading="lazy">
                                @else
                                    <div class="w-full h-full bg-zinc-100 dark:bg-zinc-800 flex flex-col items-center justify-center text-zinc-400 dark:text-zinc-500 p-4">
                                        <i class="fa-solid fa-camera text-2xl mb-1 text-zinc-300 dark:text-zinc-600"></i>
                                        <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500">DISEKITAR</span>
                                    </div>
                                @endif
                            </a>
                            <div class="space-y-1.5">
                                <span class="text-[10px] uppercase font-bold tracking-wider text-amber-800 dark:text-amber-400 block">
                                    {{ $item->category->name }}
                                </span>
                                <h3 class="font-serif-title font-bold text-lg leading-snug text-zinc-950 dark:text-zinc-100 group-hover:text-amber-800 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                    <a href="{{ route('posts.show', ['category' => $item->category->slug, 'city' => $item->city->slug, 'slug' => $item->slug]) }}" wire:navigate>
                                        {{ $item->title }}
                                    </a>
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                    {{ $item->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($item->content ?? ''), 90) }}
                                </p>
                            </div>
                        </article>
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
                 onerror="this.onerror=null; this.src='/images/placeholder.svg';"
                 class="max-h-[90vh] max-w-[90vw] rounded-xl object-contain shadow-2xl"
                 wire:click.stop>
        </div>
    @endif
</div>