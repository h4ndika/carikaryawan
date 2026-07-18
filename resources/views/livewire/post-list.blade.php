<div>
    <nav class="mb-4 text-sm text-slate-500">
        <a href="{{ route('home') }}" class="hover:text-emerald-700">Beranda</a>
        <span class="mx-1">/</span>
        <span class="text-slate-800">{{ $category->name }}</span>
        <span class="mx-1">/</span>
        <span class="text-slate-800">{{ $city->name }}</span>
    </nav>

    <header class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">
            {{ $category->name }} di {{ $city->name }}
        </h1>
        <p class="mt-1 text-slate-600">
            Daftar {{ strtolower($category->name) }} di sekitar {{ $city->name }}.
        </p>
    </header>

    @if ($posts->isEmpty())
        <p class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-slate-500">
            Belum ada post di kategori & lokasi ini.
        </p>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                @include('partials.post-card', ['post' => $post])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</div>