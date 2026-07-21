@php
    $href = route('posts.show', [
        'category' => $post->category->slug,
        'city' => $post->city->slug,
        'slug' => $post->slug,
    ]);
    $thumb = $post->thumbnail ?: null;
@endphp

<a
    href="{{ $href }}"
    wire:navigate
    class="card-lift group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
>
    <div class="relative aspect-[16/10] overflow-hidden bg-gradient-to-br from-brand-100 to-brand-50">
        @if ($thumb)
            <img
                src="{{ $thumb }}"
                alt="{{ $post->title }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            >
        @else
            <div class="flex h-full w-full items-center justify-center">
                <svg class="h-12 w-12 text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 19.5h18a1.5 1.5 0 001.5-1.5V6A1.5 1.5 0 0021 4.5H3A1.5 1.5 0 001.5 6v12A1.5 1.5 0 003 19.5z" />
                </svg>
            </div>
        @endif

        <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-brand-700 shadow-sm backdrop-blur">
            {{ $post->category->name }}
        </span>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <h3 class="line-clamp-2 text-base font-bold leading-snug text-slate-900 transition group-hover:text-brand-700">
            {{ $post->title }}
        </h3>

        @if ($post->excerpt)
            <p class="line-clamp-2 text-sm leading-relaxed text-slate-500">
                {{ $post->excerpt }}
            </p>
        @endif

        <div class="mt-auto flex items-center gap-1.5 pt-1 text-xs font-medium text-slate-500">
            <svg class="h-3.5 w-3.5 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
            </svg>
            <span class="truncate">{{ $post->city->name }}@if($post->province), {{ $post->province->name }}@endif</span>
        </div>
    </div>
</a>