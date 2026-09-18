<div class="py-8 sm:py-12 bg-[#fafaf9] dark:bg-[#0c0a09] min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="text-center mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-4">
                <i class="fa-solid fa-wrench text-zinc-400"></i>
                <span>Tools & Resources</span>
            </div>
            <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-bold text-zinc-950 dark:text-zinc-50 mb-4">
                Tools DISEKITAR
            </h1>
            <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                Kumpulan tools gratis untuk membantu perjalanan karir dan produktivitas Anda.
            </p>
        </div>

        <!-- Tools Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Tool 1: ATS CV Builder -->
            <a href="{{ route('career.cv-builder') }}" 
               wire:navigate
               class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 hover:border-emerald-500/50 dark:hover:border-emerald-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1">
                
                <!-- Background Gradient -->
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-emerald-500/5 rounded-full blur-3xl pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-file-lines text-2xl"></i>
                    </div>

                    <!-- Badge -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>AI POWERED</span>
                        </span>
                        <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">ATS Ready</span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="font-bold text-lg sm:text-xl text-zinc-900 dark:text-zinc-50 mb-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        ATS CV & Resume Builder
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                        Buat CV profesional yang lolos screening ATS dengan analisis kelengkapan real-time dan AI polish untuk ringkasan & pencapaian.
                    </p>

                    <!-- Features -->
                    <div class="space-y-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Real-time ATS score & checklist</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>AI polish ringkasan & bullet points</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-emerald-500 text-[10px]"></i>
                            <span>Export PDF, TXT & Print</span>
                        </div>
                    </div>

                    <!-- CTA Arrow -->
                    <div class="mt-5 flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold text-sm group-hover:gap-3 transition-all">
                        <span>Buka CV Builder</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </a>

            <!-- Tool 2: Interview Prep AI -->
            <a href="{{ route('career.interview-prep') }}" 
               wire:navigate
               class="group relative overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-6 sm:p-8 hover:border-violet-500/50 dark:hover:border-violet-500/50 transition-all duration-300 hover:shadow-xl hover:shadow-violet-500/10 hover:-translate-y-1">
                
                <!-- Background Gradient -->
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-violet-500/5 rounded-full blur-3xl pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative">
                    <!-- Icon -->
                    <div class="w-14 h-14 rounded-2xl bg-violet-500/10 border border-violet-500/20 flex items-center justify-center text-violet-600 dark:text-violet-400 mb-5 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-microphone text-2xl"></i>
                    </div>

                    <!-- Badge -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
                            <span>AI POWERED</span>
                        </span>
                        <span class="text-[10px] font-semibold px-2 py-1 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">STAR Method</span>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="font-bold text-lg sm:text-xl text-zinc-900 dark:text-zinc-50 mb-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
                        Interview Prep AI
                    </h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed mb-4">
                        Latih wawancara dengan pertanyaan yang disesuaikan posisi target. Dapatkan tips menjawab dengan metode STAR dan persiapan lengkap.
                    </p>

                    <!-- Features -->
                    <div class="space-y-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                            <span>15-25 pertanyaan custom per posisi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                            <span>4 kategori: General, Behavioral, Technical, Company</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check text-violet-500 text-[10px]"></i>
                            <span>Tips jawaban & STAR guide</span>
                        </div>
                    </div>

                    <!-- CTA Arrow -->
                    <div class="mt-5 flex items-center gap-2 text-violet-600 dark:text-violet-400 font-semibold text-sm group-hover:gap-3 transition-all">
                        <span>Buka Interview Prep</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </div>
                </div>
            </a>

        </div>

        <!-- Coming Soon Section -->
        <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800">
            <div class="text-center mb-8">
                <h2 class="font-bold text-xl text-zinc-800 dark:text-zinc-200 mb-2">Coming Soon</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Tools lainnya sedang dalam pengembangan</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Placeholder 1 -->
                <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 p-5 opacity-60">
                    <div class="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-3">
                        <i class="fa-solid fa-envelope text-lg"></i>
                    </div>
                    <h4 class="font-semibold text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email Writer AI</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Generate email profesional untuk berbagai kebutuhan bisnis.</p>
                </div>

                <!-- Placeholder 2 -->
                <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 p-5 opacity-60">
                    <div class="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-3">
                        <i class="fa-solid fa-calculator text-lg"></i>
                    </div>
                    <h4 class="font-semibold text-sm text-zinc-700 dark:text-zinc-300 mb-1">Salary Calculator</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Hitung estimasi gaji berdasarkan posisi dan lokasi.</p>
                </div>

                <!-- Placeholder 3 -->
                <div class="rounded-xl bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200/60 dark:border-zinc-800/60 p-5 opacity-60">
                    <div class="w-10 h-10 rounded-xl bg-zinc-200 dark:bg-zinc-800 flex items-center justify-center text-zinc-400 mb-3">
                        <i class="fa-solid fa-chart-line text-lg"></i>
                    </div>
                    <h4 class="font-semibold text-sm text-zinc-700 dark:text-zinc-300 mb-1">Career Path Planner</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Rencanakan roadmap karir dengan AI guidance.</p>
                </div>
            </div>
        </div>

        <!-- CTA Banner -->
        <div class="mt-12 rounded-2xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-950 p-8 sm:p-10 text-center border border-zinc-700/50 dark:border-zinc-800">
            <h3 class="font-serif-title text-xl sm:text-2xl font-bold text-white mb-3">
                Punya saran tool yang dibutuhkan?
            </h3>
            <p class="text-sm text-zinc-300 mb-6 max-w-xl mx-auto">
                Kami terus mengembangkan tools baru untuk membantu perjalanan karir Anda. Kirim saran Anda ke kami.
            </p>
            <a href="{{ route('page.contact') }}" 
               wire:navigate
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white hover:bg-zinc-50 text-zinc-900 font-bold text-sm transition-colors shadow-lg">
                <i class="fa-regular fa-paper-plane"></i>
                <span>Kirim Saran</span>
            </a>
        </div>

    </div>
</div>
