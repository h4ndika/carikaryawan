<div class="py-16 sm:py-20 pb-32 bg-[#fafaf9] dark:bg-[#0c0a09] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="text-center mb-20 sm:mb-24">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-6 shadow-sm">
                <i class="fa-solid fa-sparkles text-indigo-500"></i>
                <span>Tools & Resources</span>
            </div>
            <h1 class="font-serif-title text-4xl sm:text-5xl lg:text-6xl font-bold text-zinc-950 dark:text-zinc-50 mb-6 tracking-tight">
                Tools CariKaryawan
            </h1>
            <p class="text-base sm:text-lg text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                Kumpulan tools gratis berbasis AI untuk membantu perjalanan karir dan produktivitas Anda
            </p>
        </div>

        <!-- Active Tools Grid (3 columns) -->
        <div class="mb-32 sm:mb-40">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Tools Aktif</h2>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">2 tools tersedia</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Tool Card 1: ATS CV Builder -->
                <a href="{{ route('career.cv-builder') }}" 
                   wire:navigate
                   class="group relative bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden transition-all duration-300 hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-2xl hover:shadow-emerald-500/10 hover:-translate-y-1">
                    
                    <!-- Top Accent Bar -->
                    <div class="h-1.5 bg-gradient-to-r from-emerald-400 to-emerald-600"></div>
                    
                    <div class="p-8">
                        <!-- Icon -->
                        <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border-2 border-emerald-100 dark:border-emerald-900/50 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <i class="fa-solid fa-file-lines text-3xl text-emerald-600 dark:text-emerald-400"></i>
                        </div>

                        <!-- Title -->
                        <h3 class="font-bold text-xl text-zinc-900 dark:text-zinc-50 mb-3 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                            ATS CV Builder
                        </h3>

                        <!-- Description -->
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            Buat CV profesional yang lolos screening ATS dengan analisis kelengkapan real-time dan AI polish
                        </p>

                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                AI Powered
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                ATS Ready
                            </span>
                        </div>

                        <!-- CTA -->
                        <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold text-sm group-hover:gap-3 transition-all">
                            <span>Buka Tool</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <!-- Tool Card 2: Interview Prep AI -->
                <a href="{{ route('career.interview-prep') }}" 
                   wire:navigate
                   class="group relative bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden transition-all duration-300 hover:border-violet-400 dark:hover:border-violet-500 hover:shadow-2xl hover:shadow-violet-500/10 hover:-translate-y-1">
                    
                    <!-- Top Accent Bar -->
                    <div class="h-1.5 bg-gradient-to-r from-violet-400 to-violet-600"></div>
                    
                    <div class="p-8">
                        <!-- Icon -->
                        <div class="w-16 h-16 rounded-2xl bg-violet-50 dark:bg-violet-950/30 border-2 border-violet-100 dark:border-violet-900/50 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <i class="fa-solid fa-microphone text-3xl text-violet-600 dark:text-violet-400"></i>
                        </div>

                        <!-- Title -->
                        <h3 class="font-bold text-xl text-zinc-900 dark:text-zinc-50 mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                            Interview Prep AI
                        </h3>

                        <!-- Description -->
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-6">
                            Latih wawancara dengan pertanyaan yang disesuaikan posisi target, lengkap dengan tips STAR method
                        </p>

                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
                                <i class="fa-solid fa-wand-magic-sparkles"></i>
                                AI Powered
                            </span>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
                                STAR Method
                            </span>
                        </div>

                        <!-- CTA -->
                        <div class="flex items-center gap-2 text-violet-600 dark:text-violet-400 font-semibold text-sm group-hover:gap-3 transition-all">
                            <span>Buka Tool</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </a>

                <!-- Coming Soon Placeholder (to fill 3-col grid) -->
                <div class="relative bg-gradient-to-br from-zinc-50 to-zinc-100/50 dark:from-zinc-900/50 dark:to-zinc-900/30 rounded-3xl border-2 border-dashed border-zinc-300 dark:border-zinc-700 overflow-hidden">
                    <div class="h-1.5 bg-zinc-200 dark:bg-zinc-800"></div>
                    <div class="p-8 flex flex-col items-center justify-center text-center min-h-[320px]">
                        <div class="w-16 h-16 rounded-2xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-sparkles text-2xl text-zinc-400"></i>
                        </div>
                        <h3 class="font-bold text-lg text-zinc-700 dark:text-zinc-300 mb-2">Tool Berikutnya</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 max-w-[200px]">
                            Kami sedang mengembangkan tools baru untuk Anda
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="mb-32 sm:mb-40">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mb-3">Coming Soon</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-xl mx-auto">
                    Tools dalam tahap pengembangan yang akan segera hadir
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Coming Soon Card 1 -->
                <div class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 transition-all hover:border-zinc-300 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-950/30 dark:to-blue-900/20 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-envelope text-xl text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100 mb-1.5">Email Writer AI</h4>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                Generate email profesional untuk berbagai kebutuhan bisnis dengan tone yang tepat
                            </p>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                                <i class="fa-regular fa-clock"></i>
                                <span>Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coming Soon Card 2 -->
                <div class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 transition-all hover:border-zinc-300 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/30 dark:to-amber-900/20 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-calculator text-xl text-amber-600 dark:text-amber-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100 mb-1.5">Salary Calculator</h4>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                Hitung estimasi gaji yang adil berdasarkan posisi, pengalaman, dan lokasi
                            </p>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                                <i class="fa-regular fa-clock"></i>
                                <span>Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coming Soon Card 3 -->
                <div class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-6 transition-all hover:border-zinc-300 dark:hover:border-zinc-700">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-950/30 dark:to-rose-900/20 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-chart-line text-xl text-rose-600 dark:text-rose-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-base text-zinc-900 dark:text-zinc-100 mb-1.5">Career Path Planner</h4>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                Rencanakan roadmap karir Anda dengan guidance dan milestone yang jelas
                            </p>
                            <div class="mt-3 inline-flex items-center gap-1.5 text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                                <i class="fa-regular fa-clock"></i>
                                <span>Segera Hadir</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- CTA Banner -->
        <div class="rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 p-10 sm:p-12 text-center relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-1/4 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-violet-300 rounded-full blur-3xl"></div>
            </div>

            <div class="relative z-10">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/20 backdrop-blur-sm text-xs font-semibold text-white mb-4">
                    <i class="fa-regular fa-lightbulb"></i>
                    <span>Punya Ide?</span>
                </div>
                <h3 class="font-serif-title text-2xl sm:text-3xl font-bold text-white mb-4">
                    Saran Tool yang Dibutuhkan?
                </h3>
                <p class="text-sm sm:text-base text-indigo-100 mb-8 max-w-xl mx-auto leading-relaxed">
                    Kami terus mengembangkan tools baru untuk membantu perjalanan karir Anda. Kirim saran dan masukan ke kami.
                </p>
                <a href="{{ route('page.contact') }}" 
                   wire:navigate
                   class="inline-flex items-center justify-center gap-3 px-12 py-5 rounded-2xl bg-amber-400 hover:bg-amber-500 dark:bg-zinc-900 dark:hover:bg-zinc-800 text-zinc-900 dark:text-white font-bold text-base shadow-2xl shadow-amber-900/30 dark:shadow-zinc-900/50 transition-all hover:scale-105 active:scale-95">
                    <i class="fa-regular fa-paper-plane text-lg"></i>
                    <span>Kirim Saran Tool</span>
                </a>
            </div>
        </div>

    </div>
</div>
