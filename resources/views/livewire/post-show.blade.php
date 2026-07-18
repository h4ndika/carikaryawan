<div>
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1">/</span>
        <a
            href="{{ route('posts.list', ['category' => $post->category->slug, 'city' => $post->city->slug]) }}"
            class="hover:text-emerald-700"
        >
            {{ $post->category->name }} / {{ $post->city->name }}
        </a>
        <span class="mx-1">/</span>
        <span class="text-slate-800">{{ $post->title }}</span>
    </nav>

    <article>
        <header class="mb-6">
            <div class="mb-2 flex flex-wrap gap-2 text-xs text-slate-500">
                <span>{{ $post->category->name }}</span>
                <span>&middot;</span>
                <span>{{ $post->city->name }}, {{ $post->province->name }}</span>
                @if ($post->published_at)
                    <span>&middot;</span>
                    <time datetime="{{ $post->published_at }}">{{ \Illuminate\Support\Carbon::parse($post->published_at)->format('d M Y') }}</time>
                @endif
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="mt-3 text-lg text-slate-600">{{ $post->excerpt }}</p>
            @endif
        </header>

        @if ($post->thumbnail)
            <figure class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
                <button
                    type="button"
                    wire:click="openLightbox(@js($post->thumbnail))"
                    class="block w-full cursor-zoom-in"
                >
                    <img
                        src="{{ $post->thumbnail }}"
                        alt="{{ $post->title }}"
                        class="max-h-[420px] w-full object-cover"
                    >
                </button>
            </figure>
        @endif

        @if ($post->details->isNotEmpty())
            <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4">
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Detail</h2>
                <dl class="grid gap-3 sm:grid-cols-2">
                    @foreach ($post->details as $detail)
                        <div class="rounded-lg bg-slate-50 px-3 py-2">
                            <dt class="text-xs font-medium uppercase text-slate-500">{{ $detail->meta_key }}</dt>
                            <dd class="mt-0.5 text-sm text-slate-800">{{ $detail->meta_value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <div class="post-content max-w-none space-y-3 rounded-xl border border-slate-200 bg-white p-6 text-slate-800 [&_a]:text-emerald-700 [&_h2]:mt-4 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mt-3 [&_h3]:text-lg [&_h3]:font-semibold [&_img]:my-3 [&_img]:rounded-lg [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:my-2">
            {!! $post->content !!}
        </div>

        @if ($post->images->isNotEmpty())
            <section class="mt-8">
                <h2 class="mb-3 text-lg font-semibold text-slate-900">Galeri</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($post->images as $image)
                        <figure class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                            <button
                                type="button"
                                wire:click="openLightbox(@js($image->image_url))"
                                class="block w-full cursor-zoom-in"
                            >
                                <img
                                    src="{{ $image->image_url }}"
                                    alt="{{ $image->caption ?: $post->title }}"
                                    class="h-40 w-full object-cover"
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

    @if ($lightbox)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            wire:click="closeLightbox"
            x-data
            x-on:keydown.escape.window="$wire.closeLightbox()"
        >
            <button
                type="button"
                class="absolute right-4 top-4 rounded-full bg-white/10 px-3 py-1 text-sm text-white hover:bg-white/20"
                wire:click.stop="closeLightbox"
            >
                Tutup
            </button>
            <img
                src="{{ $lightbox }}"
                alt="Preview"
                class="max-h-[90vh] max-w-full rounded-lg object-contain"
                wire:click.stop
            >
        </div>
    @endif
</div>