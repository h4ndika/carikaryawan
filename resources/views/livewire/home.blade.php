<div>
    <section class="mb-10">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Apa saja di sekitarmu</h1>
        <p class="mt-2 max-w-2xl text-slate-600">
            Temukan tempat, kuliner, wisata, dan lowongan berdasarkan kota di sekitarmu.
        </p>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mb-10">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Kategori</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm text-slate-700">
                        {{ $category->name }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    <section>
        <h2 class="mb-4 text-xl font-semibold text-slate-900">Terbaru</h2>

        @if ($posts->isEmpty())
            <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-slate-500">
                Belum ada post published.
            </p>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    @include('partials.post-card', ['post' => $post])
                @endforeach
            </div>
        @endif
    </section>
</div>