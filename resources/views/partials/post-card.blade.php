@php
    $meta = $post->details->mapWithKeys(fn ($detail) => [strtolower(trim($detail->meta_key)) => trim($detail->meta_value)]);
    $company = $meta->get('nama_perusahaan') ?: $meta->get('perusahaan');
    $salary = $meta->get('gaji');
    $jobType = $meta->get('tipe_pekerjaan');
@endphp

<article class="group rounded-xl bg-white dark:bg-zinc-900 p-5 sm:p-6 transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800/70">
    <a href="{{ $post->url() }}" wire:navigate class="flex items-start gap-4">
        <div class="relative shrink-0 w-12 h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center overflow-hidden" aria-hidden="true">
            <span class="text-lg font-bold text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($company ?: $post->title, 0, 1)) }}</span>
            @if ($post->thumbnail)
                <img src="{{ $post->thumbnail }}" alt="" class="absolute inset-0 w-full h-full object-contain bg-white p-1" loading="lazy" onerror="this.remove()">
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-base sm:text-lg font-bold leading-snug break-words group-hover:text-blue-700 dark:group-hover:text-blue-400">{{ $post->title }}</h3>
            @if ($company)
                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300 break-words">{{ $company }}</p>
            @endif
            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                <span>{{ $post->city?->name ?? 'Indonesia' }}</span>
                @if ($jobType)<span>{{ $jobType }}</span>@endif
            </div>
            @if ($salary)
                <p class="mt-2 text-sm font-semibold break-words">{{ $salary }}</p>
            @endif
            <div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->diffForHumans() }}</time>
                @endif
                @if ($post->isExpired())
                    <span class="rounded px-2 py-1 bg-red-50 dark:bg-red-950 text-red-700 dark:text-red-300 font-semibold">Ditutup</span>
                @endif
            </div>
        </div>
    </a>
</article>
