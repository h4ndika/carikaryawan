<div class="space-y-4 p-2">
    @php
        $thumbnail = $post->thumbnail;
        $galleryImages = $images ?? collect();
    @endphp

    @if(!empty($thumbnail) || $galleryImages->isNotEmpty())
        <div class="flex items-center justify-between border-b pb-2 border-gray-200 dark:border-gray-700">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                Total {{ $galleryImages->count() }} Foto Tersimpan
            </span>
            @if(!empty($post->place_id))
                <span class="text-xs text-gray-500">
                    Place ID: {{ $post->place_id }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto p-1">
            @forelse($galleryImages as $index => $img)
                <div class="group relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shadow-sm hover:shadow-md transition">
                    <img 
                        src="{{ $img->image_url }}" 
                        alt="{{ $img->caption ?: $post->title }}" 
                        class="w-full h-36 object-cover group-hover:scale-105 transition duration-200"
                        loading="lazy"
                        onerror="this.src='https://placehold.co/400x300?text=Gambar+Rusak'"
                    />
                    
                    <div class="absolute top-2 left-2 flex gap-1 items-center">
                        <span class="bg-black/60 backdrop-blur-sm text-white text-[10px] font-medium px-2 py-0.5 rounded-full">
                            #{{ $index + 1 }}
                        </span>
                        @if($img->image_url === $thumbnail)
                            <span class="bg-emerald-600/90 backdrop-blur-sm text-white text-[10px] font-semibold px-2 py-0.5 rounded-full">
                                Thumbnail
                            </span>
                        @endif
                    </div>

                    <a 
                        href="{{ $img->image_url }}" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition text-white text-xs font-medium"
                    >
                        <span class="bg-black/70 px-2.5 py-1 rounded-md">Buka Ukuran Penuh ↗</span>
                    </a>
                </div>
            @empty
                @if(!empty($thumbnail))
                    <div class="group relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 shadow-sm">
                        <img 
                            src="{{ $thumbnail }}" 
                            alt="{{ $post->title }}" 
                            class="w-full h-36 object-cover"
                            loading="lazy"
                        />
                        <div class="absolute top-2 left-2">
                            <span class="bg-emerald-600 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full">
                                Thumbnail
                            </span>
                        </div>
                        <a 
                            href="{{ $thumbnail }}" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition text-white text-xs font-medium"
                        >
                            <span class="bg-black/70 px-2.5 py-1 rounded-md">Buka Ukuran Penuh ↗</span>
                        </a>
                    </div>
                @endif
            @endforelse
        </div>
    @else
        <div class="py-12 text-center text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="mt-3 text-sm font-medium">Belum ada foto untuk tempat ini.</p>
            <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Scrape Foto" untuk mengambil foto secara otomatis.</p>
        </div>
    @endif
</div>
