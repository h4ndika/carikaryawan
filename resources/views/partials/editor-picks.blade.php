<div class="space-y-3">
    @forelse ($posts as $pick)
        @include('partials.post-card', ['post' => $pick])
    @empty
        <p class="text-sm text-zinc-600 dark:text-zinc-400">Belum ada lowongan.</p>
    @endforelse
</div>
