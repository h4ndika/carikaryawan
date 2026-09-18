@props(['post'])

@php
    $companyName = $post->company_name ?: ($post->job_company ?: 'Perusahaan');
@endphp

<!-- Interview Prep AI Banner -->
<div class="rounded-2xl border border-violet-200 dark:border-violet-800/60 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/20 dark:to-purple-950/20 p-6 hover:shadow-lg transition-all duration-300">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
        <!-- Icon -->
        <div class="shrink-0 w-14 h-14 rounded-2xl bg-violet-600 dark:bg-violet-500 flex items-center justify-center shadow-lg shadow-violet-600/30">
            <i class="fa-solid fa-microphone text-white text-2xl"></i>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Interview Prep AI</h3>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-600 text-white">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>AI Powered</span>
                </span>
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                Latih wawancara dengan pertanyaan yang disesuaikan untuk posisi <strong class="text-violet-700 dark:text-violet-400">{{ $post->title }}</strong> di {{ $companyName }}. Dapatkan tips STAR method lengkap.
            </p>
        </div>

        <!-- CTA Button -->
        <div class="shrink-0 w-full sm:w-auto">
            <a href="{{ route('career.interview-prep', ['job' => $post->id]) }}" 
               wire:navigate
               class="block w-full sm:w-auto text-center px-6 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white font-bold text-sm shadow-md shadow-violet-600/25 transition-all hover:scale-105 active:scale-95">
                <i class="fa-solid fa-arrow-right mr-2"></i>
                <span>Latih Interview</span>
            </a>
        </div>
    </div>
</div>
