<div class="min-h-screen py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb & Category -->
        <nav aria-label="Jejak halaman" class="flex flex-wrap items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300 font-medium mb-6">
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
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                        <i class="fa-solid fa-location-dot text-blue-700 dark:text-blue-400 text-[11px]"></i>
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

            <!-- Source Metadata & Share -->
            <div class="w-full pt-4 border-t border-zinc-200 dark:border-zinc-800 flex flex-wrap items-center justify-between gap-4 text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                        C
                    </div>
                    <div>
                        <span class="text-zinc-900 dark:text-zinc-200 font-bold block">CariKaryawan</span>
                        <span>{{ $post->published_at ? \Illuminate\Support\Carbon::parse($post->published_at)->translatedFormat('d F Y') : date('d F Y') }}</span>
                    </div>
                </div>

                <!-- Action & Share Buttons -->
                <div class="flex flex-wrap items-center gap-2">
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


        <!-- Main 2-Column Content Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- Left Column: Job Content (8 cols) -->
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

                <!-- 1-Click AI Career Tools for Jobs -->
                @if ($post->isJob())
                    <div class="mt-8 space-y-4">
                        <x-job-cover-letter-modal :post="$post" />
                        <x-job-cv-modal :post="$post" />
                        <x-job-interview-prep-banner :post="$post" />
                    </div>
                @endif

                <!-- Career Toolkit / Afiliasi Loker Section -->
                @if ($post->isJob())
                    @php
                        $affiliateEnabled = (bool) \App\Models\Setting::get('job_affiliate_enabled', true);
                        $affiliateTitle = \App\Models\Setting::get('job_affiliate_title', 'Persiapan Lamar Kerja (Toolkit Karir)');
                        $affiliateSubtitle = \App\Models\Setting::get('job_affiliate_subtitle', 'Tingkatkan peluang lolos seleksi dengan referensi template CV dan materi pendukung pilihan berikut:');
                        $rawItems = \App\Models\Setting::get('job_affiliate_items');
                        $affiliateItems = [];
                        if (!empty($rawItems)) {
                            $decoded = is_array($rawItems) ? $rawItems : json_decode($rawItems, true);
                            if (is_array($decoded)) {
                                $affiliateItems = array_values(array_filter($decoded, function ($item) {
                                    return !empty($item['url']) && ($item['is_active'] ?? true);
                                }));
                            }
                        }
                    @endphp

                    @if ($affiliateEnabled && !empty($affiliateItems))
                        <section class="mt-8 bg-white dark:bg-zinc-900 p-6 sm:p-8 rounded-xl">
                            <div class="flex items-start gap-3.5 mb-5">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0 text-lg mt-0.5">
                                    <i class="fa-solid fa-briefcase"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-serif-title text-lg sm:text-xl font-bold text-zinc-950 dark:text-zinc-50">
                                            {{ $affiliateTitle }}
                                        </h3>
                                        <span class="bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                                            Rekomendasi
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-1 leading-relaxed">
                                        {{ $affiliateSubtitle }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                @foreach ($affiliateItems as $item)
                                    @php
                                        $platform = strtolower($item['platform'] ?? 'custom');
                                        $platformBadge = match($platform) {
                                            'shopee' => ['name' => 'Shopee', 'color' => 'bg-[#EE4D2D] text-white', 'icon' => 'fa-solid fa-bag-shopping'],
                                            'tokopedia' => ['name' => 'Tokopedia', 'color' => 'bg-[#03AC0E] text-white', 'icon' => 'fa-solid fa-bag-shopping'],
                                            'skill_academy' => ['name' => 'Skill Academy', 'color' => 'bg-sky-600 text-white', 'icon' => 'fa-solid fa-graduation-cap'],
                                            'dicoding' => ['name' => 'Dicoding', 'color' => 'bg-indigo-600 text-white', 'icon' => 'fa-solid fa-code'],
                                            'tiktok' => ['name' => 'TikTok Shop', 'color' => 'bg-black text-white', 'icon' => 'fa-brands fa-tiktok'],
                                            'gramedia' => ['name' => 'Gramedia', 'color' => 'bg-blue-700 text-white', 'icon' => 'fa-solid fa-book'],
                                            default => ['name' => 'Partner Resmi', 'color' => 'bg-zinc-800 text-white', 'icon' => 'fa-solid fa-arrow-up-right-from-square'],
                                        };
                                    @endphp
                                    <a href="{{ $item['url'] }}" 
                                       target="_blank" 
                                       rel="nofollow sponsored noopener noreferrer"
                                       class="group relative flex flex-col justify-between p-4 rounded-xl bg-white dark:bg-zinc-800/80 hover:bg-amber-50/50 dark:hover:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/70 hover:border-amber-400/80 dark:hover:border-amber-600/60 shadow-2xs hover:shadow-md transition-all duration-200">
                                        <div>
                                            <div class="flex items-center justify-between gap-2 mb-2">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $platformBadge['color'] }}">
                                                    <i class="{{ $platformBadge['icon'] }} text-[9px]"></i>
                                                    {{ $platformBadge['name'] }}
                                                </span>
                                                @if (!empty($item['badge']))
                                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                        {{ $item['badge'] }}
                                                    </span>
                                                @endif
                                            </div>
                                            <h4 class="font-bold text-xs sm:text-sm text-zinc-900 dark:text-zinc-100 group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors line-clamp-2">
                                                {{ $item['title'] }}
                                            </h4>
                                            @if (!empty($item['description']))
                                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-2 leading-relaxed">
                                                    {{ $item['description'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="mt-3 pt-2.5 border-t border-zinc-100 dark:border-zinc-700/50 flex items-center justify-between text-[11px] font-semibold text-amber-700 dark:text-amber-400 group-hover:text-amber-800 dark:group-hover:text-amber-300">
                                            <span>Lihat Rekomendasi</span>
                                            <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endif

                <!-- Ad Slot: Di Atas Galeri -->
                <x-ad-slot slotName="ad_post_before_gallery" />
            </div>

            <!-- Right Column: Job Details (4 cols) -->
            <aside class="lg:col-span-4 space-y-8 sticky top-24">
                
                <!-- Ad Slot: Sidebar Atas Detail Post -->
                <x-ad-slot slotName="ad_post_sidebar_top" class="!mt-0 !mb-6" />

                <!-- Job Summary Card -->
                @php
                    $applyUrl = $post->website ?: ($post->details?->first(fn($d) => in_array(strtolower(trim($d->meta_key)), ['tautan_lamaran', 'link_lamaran', 'url_lamaran', 'link_pendaftaran', 'url_lowongan', 'website', 'url', 'link']))?->meta_value);
                @endphp
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-xs space-y-6">
                    <div class="pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="text-sm font-semibold text-blue-700 dark:text-blue-400 block mb-2">
                            Informasi lowongan
                        </span>
                        <h3 class="font-serif-title font-bold text-xl text-zinc-950 dark:text-zinc-50">
                            {{ $post->title }}
                        </h3>

                    </div>

                    @if ($post->isJob() && $applyUrl)
                        <div class="space-y-2.5">
                            @if ($post->isExpired())
                                <button disabled class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 text-xs font-semibold tracking-wide cursor-not-allowed border border-zinc-200 dark:border-zinc-700/60">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                    <span>Pendaftaran Telah Ditutup</span>
                                </button>
                            @else
                                <a href="{{ $applyUrl }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-brand hover:bg-blue-800 text-white text-sm font-semibold transition-colors">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                    <span>Lamar Sekarang</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] opacity-70"></i>
                                </a>

                                <button type="button" 
                                        @click="window.dispatchEvent(new CustomEvent('open-cover-letter-modal'))"
                                        class="cursor-pointer w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 text-xs font-semibold transition-colors">
                                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                    <span>Buat Surat Lamaran AI</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-zinc-500 dark:text-zinc-400">Lokasi</dt>
                            <dd class="mt-1 font-semibold">{{ $post->city?->name ?? 'Indonesia' }}</dd>
                        </div>
                        @if ($post->published_at)
                            <div>
                                <dt class="text-zinc-500 dark:text-zinc-400">Publikasi aktif sampai</dt>
                                <dd class="mt-1 font-semibold">{{ ($post->expired_at ?? $post->published_at->copy()->addDays(21))->translatedFormat('d F Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                    <!-- Job Info Attributes -->
                    <div class="space-y-5 text-sm overflow-hidden">
                        @if ($post->formatted_address)
                            <div class="flex items-start gap-3">
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-blue-700 dark:text-blue-400 flex-shrink-0 mt-0.5">
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
                                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-blue-700 dark:text-blue-400 flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">Kontak & Telepon</span>
                                    <a href="tel:{{ $post->phone }}" class="text-zinc-900 dark:text-zinc-100 font-semibold hover:text-blue-700 dark:hover:text-blue-400 transition-colors break-words">
                                        {{ $post->phone }}
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
                                    'lokasi' => ['label' => 'Lokasi / Alamat', 'icon' => 'fa-solid fa-map-pin'],
                                    'alamat' => ['label' => 'Alamat', 'icon' => 'fa-solid fa-map-pin'],
                                    'website' => ['label' => 'Website', 'icon' => 'fa-solid fa-globe'],
                                    'telepon' => ['label' => 'Telepon', 'icon' => 'fa-solid fa-phone'],
                                    'kontak' => ['label' => 'Kontak', 'icon' => 'fa-solid fa-phone'],
                                ];
                            @endphp

                            <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 space-y-4">
                                @foreach ($post->details->sortBy(fn ($detail) => match (strtolower(trim($detail->meta_key))) {
                                    'nama_perusahaan', 'perusahaan' => 0,
                                    'gaji' => 1,
                                    'tipe_pekerjaan' => 2,
                                    default => 3,
                                }) as $detail)
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
                                        <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-blue-700 dark:text-blue-400 flex-shrink-0 mt-0.5">
                                            <i class="{{ $metaConfig['icon'] }} text-xs"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 block mb-0.5">
                                                {{ $metaConfig['label'] }}
                                            </span>
                                            @if ($isUrl)
                                                <a href="{{ $detail->meta_value }}" target="_blank" rel="noopener noreferrer" class="text-blue-700 dark:text-blue-400 font-semibold hover:underline inline-flex items-center gap-1 break-all">
                                                    Kunjungi Tautan <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                </a>
                                            @else
                                                <span class="text-zinc-800 dark:text-zinc-200 font-medium leading-relaxed block break-words">{{ $detail->meta_value }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Back to City Jobs -->
                <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-xl p-5 text-center">
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-3">Mencari lowongan lain di {{ $post->city->name }}?</p>
                    <a href="{{ route('search', ['city' => $post->city->slug]) }}" 
                       wire:navigate
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-blue-700 dark:hover:text-blue-400 transition-colors">
                        Lihat Semua Lowongan di {{ $post->city->name }} <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </aside>
        </div>
        <!-- Related Jobs in Same City -->
        @if (isset($related) && $related->isNotEmpty())
            <section class="mt-20 pt-12 border-t border-zinc-200 dark:border-zinc-800">
                <div class="mb-8 flex items-baseline justify-between">
                    <div>
                        <h2 class="font-serif-title text-2xl sm:text-3xl font-bold text-zinc-950 dark:text-zinc-50">
                            Lowongan lain di {{ $post->city?->name ?? 'Indonesia' }}
                        </h2>
                    </div>
                    @if ($post->city)
                        <a href="{{ route('search', ['city' => $post->city->slug]) }}" 
                           wire:navigate
                           class="text-xs font-bold text-zinc-900 dark:text-zinc-200 hover:text-blue-700 dark:hover:text-blue-400 hidden sm:inline-flex items-center gap-1">
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
</div>