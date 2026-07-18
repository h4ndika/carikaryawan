@php
    $url = route('posts.show', [
        'category' => $post->category->slug,
        'city' => $post->city->slug,
        'slug' => $post->slug,
    ]);
@endphp
<article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-emerald-300 hover:shadow">
    <a href="{{ $url }}" class="block">
        @if ($post->thumbnail)
            <img
                src="{{ $post->thumbnail }}"
                alt="{{ $post->title }}"
                class="h-40 w-full object-cover"
                loading="lazy"
            >
        @else
            <div class="flex h-40 w-full items-center justify-center bg-slate-100 text-sm text-slate-400">
                No image
            </div>
        @endif
        <div class="p-4">
            <div class="mb-1 flex flex-wrap gap-2 text-xs text-slate-500">
                <span>{{ $post->category->name }}</span>
                <span>&middot;</span>
                <span>{{ $post->city->name }}</span>
            </div>
            <h3 class="line-clamp-2 text-base font-semibold text-slate-900">{{ $post->title }}</h3>
            @if ($post->excerpt)
                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $post->excerpt }}</p>
            @endif
        </div>
    </a>
</article>