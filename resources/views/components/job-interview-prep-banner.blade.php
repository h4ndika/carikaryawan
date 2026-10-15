@props(['post'])

<section class="rounded-xl bg-white dark:bg-zinc-900 p-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-5">
        <div class="flex-1 min-w-0">
            <h3 class="font-bold text-lg">Siapkan jawaban interview</h3>
            <p class="mt-2 text-base text-zinc-600 dark:text-zinc-300 leading-relaxed break-words">
                Latihan pertanyaan untuk posisi {{ $post->title }}, dengan panduan menyusun jawaban metode STAR.
            </p>
        </div>
        <a href="{{ route('career.interview-prep', ['job' => $post->id]) }}" wire:navigate class="shrink-0 text-center px-5 py-3 rounded-lg bg-brand hover:bg-blue-800 text-white font-semibold text-sm transition-colors">
            Latihan interview
        </a>
    </div>
</section>
