<div>
    {{-- Hero image strip --}}
    <section class="relative bg-brand-950">
        @if ($post->thumbnail)
            <div class="relative mx-auto max-w-6xl">
                <button
                    type="button"
                    wire:click="openLightbox(@js($post->thumbnail))"
                    class="group relative block w-full cursor-zoom-in overflow-hidden"
                >
                    <img
                        src="{{ $post->thumbnail }}"
                        alt="{{ $post->title }}"
                        class="max-h-[380px] w-full object-cover opacity-95 transition duration-500 group-hover:scale-[1.02] sm:max-h-[460px]"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-950/80 via-brand-950/20 to-transparent"></div>
                    <span class="absolute bottom-4 right-4 rounded-full bg-black/50 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur">
                        Perbesar
                    </span>
                </button>
            </div>
        @else
            <div class="h-32 bg-gradient-to-br from-brand-800 to-brand-950 sm:h-40"></div>
        @endif
    </section>

    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        {{-- Title card overlapping hero --}}
        <article class="relative {{ $post->thumbnail ? '-mt-16 sm:-mt-20' : '-mt-8' }}">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xl shadow-slate-200/60 sm:p-8">
                <nav class="mb-4 flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
                    <a href="{{ route('home') }}" wire:navigate class="hover:text-brand-700">Beranda</a>
                    <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <a
                        href="{{ route('posts.list', ['category' => $post->category->slug, 'city' => $post->city->slug]) }}"
                        wire:navigate
                        class="hover:text-brand-700"
                    >
                        {{ $post->category->name }} / {{ $post->city->name }}
                    </a>
                </nav>

                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-brand-700">
                        {{ $post->category->name }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                        <svg class="h-3.5 w-3.5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        {{ $post->city->name }}@if($post->province), {{ $post->province->name }}@endif
                    </span>
                    @if ($post->published_at)
                        <span class="text-xs text-slate-400">
                            · {{ \Illuminate\Support\Carbon::parse($post->published_at)->format('d M Y') }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="mt-3 text-base leading-relaxed text-slate-600 sm:text-lg">
                        {{ $post->excerpt }}
                    </p>
                @endif
            </div>

            {{-- Meta details grid --}}
            @if ($post->details->isNotEmpty())
                <section class="mt-6">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-400">Informasi</h2>
                    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($post->details as $detail)
                            <div class="rounded-xl border border-slate-200/80 bg-white px-4 py-3 shadow-sm">
                                <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $detail->meta_key }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $detail->meta_value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endif

            {{-- Body --}}
            <div class="post-content mt-6 max-w-none space-y-3 rounded-2xl border border-slate-200/80 bg-white p-5 text-slate-800 shadow-sm sm:p-8 [&_a]:text-brand-700 [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-bold [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-bold [&_img]:my-4 [&_img]:rounded-xl [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:my-2">
                {!! $post->content !!}
            </div>

            {{-- Gallery --}}
            @if ($post->images->isNotEmpty())
                <section class="mt-8">
                    <h2 class="mb-4 text-lg font-extrabold text-slate-900">Galeri</h2>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($post->images as $image)
                            <figure class="group overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                                <button
                                    type="button"
                                    wire:click="openLightbox(@js($image->image_url))"
                                    class="block w-full cursor-zoom-in"
                                >
                                    <img
                                        src="{{ $image->image_url }}"
                                        alt="{{ $image->caption ?: $post->title }}"
                                        class="h-44 w-full object-cover transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    >
                                </button>
                                @if ($image->caption)
                                    <figcaption class="px-3 py-2 text-xs text-slate-500">{{ $image->caption }}</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>

        {{-- Related --}}
        @if ($related->isNotEmpty())
            <section class="mt-12">
                <div class="mb-5 flex items-end justify-between">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900">
                            {{ $post->category->name }} lain di {{ $post->city->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">Mungkin kamu juga suka</p>
                    </div>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        @include('partials.post-card', ['post' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Lightbox --}}
    @if ($lightbox)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
            wire:click="closeLightbox"
            x-data
            x-on:keydown.escape.window="$wire.closeLightbox()"
        >
            <button
                type="button"
                class="absolute right-4 top-4 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/20"
                wire:click.stop="closeLightbox"
            >
                Tutup ✕
            </button>
            <img
                src="{{ $lightbox }}"
                alt="Preview"
                class="max-h-[90vh] max-w-full rounded-xl object-contain shadow-2xl"
                wire:click.stop
            >
        </div>
    @endif
</div>