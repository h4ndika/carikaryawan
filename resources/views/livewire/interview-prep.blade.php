<div class="py-8 sm:py-12 bg-[#fafaf9] dark:bg-[#0c0a09] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="interviewPrepApp({
            generateUrl: @js(route('career.interview-prep.generate')),
            csrfToken: @js(csrf_token()),
            jobPost: @js($jobPost ? [
                'title' => $jobPost->title,
                'company' => $jobPost->company_name ?: ($jobPost->job_company ?: ''),
                'description' => mb_substr(strip_tags($jobPost->content), 0, 3000),
            ] : null),
         })"
         x-cloak>

        <!-- Page Header -->
        <div class="mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20">
                            <i class="fa-solid fa-microphone text-[10px]"></i>
                            <span>AI Interview Coach</span>
                        </span>
                    </div>
                    <h1 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold text-zinc-950 dark:text-zinc-50 mt-2.5">
                        Interview Prep AI
                    </h1>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1.5 max-w-2xl leading-relaxed">
                        Latih wawancara kerja dengan pertanyaan yang disesuaikan posisi target. Dapatkan pertanyaan general, behavioral, dan teknis lengkap dengan tips menjawab.
                    </p>
                </div>

                <!-- Export Buttons (show after results) -->
                <div x-show="hasResult" class="flex items-center gap-2.5 shrink-0 flex-wrap">
                    <button type="button"
                            @click="copyAll()"
                            class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-zinc-200/80 hover:bg-zinc-300/80 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all active:scale-95">
                        <i :class="isCopied ? 'fa-solid fa-check text-emerald-500' : 'fa-regular fa-copy'" class="text-[11px]"></i>
                        <span x-text="isCopied ? 'Tersalin!' : 'Salin Semua'"></span>
                    </button>
                    <button type="button"
                            @click="downloadTxt()"
                            class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition-all active:scale-95">
                        <i class="fa-solid fa-download text-xs"></i>
                        <span>Unduh TXT</span>
                    </button>
                    <button type="button"
                            @click="printQuestions()"
                            class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold shadow-md shadow-violet-600/25 transition-all active:scale-95">
                        <i class="fa-solid fa-print text-xs"></i>
                        <span>Cetak / PDF</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main 2-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- LEFT: Form + Results (7 cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Input Form Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400 font-bold text-xs flex items-center justify-center">
                            <i class="fa-solid fa-bullseye text-[11px]"></i>
                        </span>
                        <div>
                            <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Target Posisi & Preferensi</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Tentukan posisi dan tingkat wawancara</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Posisi / Jabatan Target <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       x-model="form.job_title"
                                       placeholder="Contoh: Frontend Developer"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-violet-500/30 focus:border-violet-500 text-zinc-900 dark:text-white transition-all">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Nama Perusahaan
                                </label>
                                <input type="text"
                                       x-model="form.company"
                                       placeholder="Contoh: PT Inovasi Digital"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-violet-500/30 focus:border-violet-500 text-zinc-900 dark:text-white transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Level Kandidat
                                </label>
                                <select x-model="form.level"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-violet-500/30 focus:border-violet-500 text-zinc-900 dark:text-white transition-all">
                                    <option value="fresh">Fresh Graduate / Entry</option>
                                    <option value="mid" selected>Mid-Level (2-5 thn)</option>
                                    <option value="senior">Senior / Lead (5+ thn)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Fokus Pertanyaan
                                </label>
                                <select x-model="form.focus"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-violet-500/30 focus:border-violet-500 text-zinc-900 dark:text-white transition-all">
                                    <option value="all" selected>Semua Kategori</option>
                                    <option value="general">General / Umum</option>
                                    <option value="behavioral">Behavioral (STAR)</option>
                                    <option value="technical">Teknis / Fungsional</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Bahasa
                                </label>
                                <select x-model="form.language"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-violet-500/30 focus:border-violet-500 text-zinc-900 dark:text-white transition-all">
                                    <option value="id">Bahasa Indonesia</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>

                        <!-- Error -->
                        <div x-show="errorMessage" class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>

                        <!-- Generate Button -->
                        <div class="pt-2">
                            <button type="button"
                                    @click="generate()"
                                    :disabled="isLoading"
                                    class="cursor-pointer w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white text-sm font-bold shadow-lg shadow-violet-600/25 border border-violet-500 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs">
                                    <i x-show="!isLoading" class="fa-solid fa-wand-magic-sparkles"></i>
                                    <i x-show="isLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                                </span>
                                <span x-text="isLoading ? 'Menyusun Pertanyaan Interview...' : 'Generate Pertanyaan Interview'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Results: Question Categories (Accordion) -->
                <div x-show="hasResult" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                            <span>Pertanyaan Interview</span>
                            <span class="text-[10px] text-zinc-500 dark:text-zinc-400 font-normal" x-text="'(' + totalQuestions + ' pertanyaan)'"></span>
                        </span>
                        <button type="button"
                                @click="toggleAllAccordions()"
                                class="cursor-pointer text-[11px] font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                            <span x-text="allExpanded ? 'Tutup Semua' : 'Buka Semua'"></span>
                        </button>
                    </div>

                    <!-- Category: General -->
                    <template x-for="(cat, catIdx) in categories" :key="cat.key">
                        <div x-show="questions[cat.key] && questions[cat.key].length > 0"
                             class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs overflow-hidden">

                            <!-- Category Header (Accordion Toggle) -->
                            <button type="button"
                                    @click="toggleAccordion(cat.key)"
                                    class="cursor-pointer w-full px-5 py-4 flex items-center justify-between text-left hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm shrink-0"
                                          :class="cat.iconBg">
                                        <i :class="cat.icon"></i>
                                    </span>
                                    <div>
                                        <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-100" x-text="cat.label"></h3>
                                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400" x-text="(questions[cat.key]?.length || 0) + ' pertanyaan · ' + cat.desc"></p>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-down text-xs text-zinc-400 transition-transform duration-200"
                                   :class="expanded[cat.key] ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Category Body -->
                            <div x-show="expanded[cat.key]"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="border-t border-zinc-100 dark:border-zinc-800">
                                <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                    <template x-for="(item, qIdx) in questions[cat.key]" :key="qIdx">
                                        <div class="px-5 py-4 hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <span class="w-6 h-6 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                                      x-text="qIdx + 1"></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 leading-relaxed" x-text="item.q"></p>
                                                    <!-- Hint Toggle -->
                                                    <div class="mt-2">
                                                        <button type="button"
                                                                @click="item._showHint = !item._showHint"
                                                                class="cursor-pointer inline-flex items-center gap-1.5 text-[11px] font-semibold text-violet-600 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">
                                                            <i :class="item._showHint ? 'fa-solid fa-eye-slash' : 'fa-regular fa-lightbulb'" class="text-[10px]"></i>
                                                            <span x-text="item._showHint ? 'Sembunyikan Tips' : 'Tampilkan Tips Jawab'"></span>
                                                        </button>
                                                        <div x-show="item._showHint"
                                                             x-transition
                                                             class="mt-2 p-3 rounded-xl bg-violet-50/50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/30 text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                                            <i class="fa-regular fa-lightbulb text-amber-500 mr-1"></i>
                                                            <span x-text="item.hint"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

            </div>

            <!-- RIGHT: Tips & Guide (5 cols, sticky) -->
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">

                <!-- STAR Method Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 pb-3.5 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-violet-500"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Metode STAR</h3>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-bold flex items-center justify-center shrink-0">S</span>
                            <div>
                                <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Situation</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Deskripsikan konteks atau latar belakang situasi yang kamu hadapi.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold flex items-center justify-center shrink-0">T</span>
                            <div>
                                <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Task</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Jelaskan tanggung jawab atau tugas spesifik kamu.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">A</span>
                            <div>
                                <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Action</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Ceritakan langkah konkret yang kamu ambil untuk menyelesaikan masalah.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400 text-xs font-bold flex items-center justify-center shrink-0">R</span>
                            <div>
                                <p class="text-xs font-bold text-zinc-900 dark:text-zinc-100">Result</p>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Sampaikan hasil konkret dan terukur dari tindakanmu.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Tips Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 pb-3.5 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Tips Wawancara</h3>
                    </div>
                    <div class="mt-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-[10px]"></i>
                            <span>Riset perusahaan terlebih dahulu — budaya, produk, misi, dan berita terbaru.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-[10px]"></i>
                            <span>Siapkan 3-5 cerita pencapaian nyata yang bisa dipakai untuk berbagai pertanyaan behavioral.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-[10px]"></i>
                            <span>Latih jawaban dengan keras (bicara sendiri) — bukan hanya di pikiran.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-[10px]"></i>
                            <span>Jangan pernah menjelek-jelekkan perusahaan atau atasan sebelumnya.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-[10px]"></i>
                            <span>Siapkan 2-3 pertanyaan balik untuk interviewer di akhir sesi.</span>
                        </div>
                    </div>
                </div>

                <!-- Common Mistakes Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-xs">
                    <div class="flex items-center gap-2 pb-3.5 border-b border-zinc-100 dark:border-zinc-800">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Kesalahan Umum</h3>
                    </div>
                    <div class="mt-4 space-y-3 text-xs text-zinc-600 dark:text-zinc-400">
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 text-[10px]"></i>
                            <span>Menjawab terlalu panjang tanpa inti — ideal 1-2 menit per jawaban.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 text-[10px]"></i>
                            <span>Menjawab "Saya tidak punya kelemahan" — tidak realistis dan menunjukkan kurangnya self-awareness.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 text-[10px]"></i>
                            <span>Tidak menyiapkan pertanyaan balik — memberi kesan tidak antusias.</span>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 text-[10px]"></i>
                            <span>Menghafalkan jawaban kata per kata — terdengar kaku, lebih baik hafal poin-poinnya.</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
(() => {
    const defineComponent = () => {
        if (!window.Alpine) return;

        window.Alpine.data('interviewPrepApp', (config) => ({
            isLoading: false,
            hasResult: false,
            isCopied: false,
            errorMessage: '',
            allExpanded: true,

            form: {
                job_title: config.jobPost?.title || '',
                company: config.jobPost?.company || '',
                job_description: config.jobPost?.description || '',
                level: 'mid',
                focus: 'all',
                language: 'id'
            },

            questions: {
                general: [],
                behavioral: [],
                technical: [],
                company_specific: []
            },

            expanded: {
                general: true,
                behavioral: true,
                technical: true,
                company_specific: true
            },

            categories: [
                {
                    key: 'general',
                    label: 'Pertanyaan Umum (General)',
                    desc: 'Perkenalan, motivasi, ekspektasi',
                    icon: 'fa-solid fa-comments',
                    iconBg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400'
                },
                {
                    key: 'behavioral',
                    label: 'Behavioral (STAR Method)',
                    desc: 'Situasi nyata, teamwork, leadership',
                    icon: 'fa-solid fa-brain',
                    iconBg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                },
                {
                    key: 'technical',
                    label: 'Teknis / Fungsional',
                    desc: 'Keahlian spesifik posisi target',
                    icon: 'fa-solid fa-code',
                    iconBg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                },
                {
                    key: 'company_specific',
                    label: 'Spesifik Perusahaan',
                    desc: 'Riset perusahaan, budaya kerja',
                    icon: 'fa-solid fa-building',
                    iconBg: 'bg-violet-500/10 text-violet-600 dark:text-violet-400'
                }
            ],

            get totalQuestions() {
                let total = 0;
                for (const key of Object.keys(this.questions)) {
                    total += (this.questions[key]?.length || 0);
                }
                return total;
            },

            toggleAccordion(key) {
                this.expanded[key] = !this.expanded[key];
            },

            toggleAllAccordions() {
                this.allExpanded = !this.allExpanded;
                for (const key of Object.keys(this.expanded)) {
                    this.expanded[key] = this.allExpanded;
                }
            },

            async generate() {
                if (!this.form.job_title.trim()) {
                    this.errorMessage = 'Posisi / jabatan target wajib diisi.';
                    return;
                }

                this.isLoading = true;
                this.errorMessage = '';
                this.hasResult = false;

                try {
                    const res = await fetch(config.generateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Gagal menyusun pertanyaan. Silakan coba lagi.');
                    }

                    // Add _showHint flag to each question item
                    for (const key of Object.keys(data.data)) {
                        if (Array.isArray(data.data[key])) {
                            data.data[key] = data.data[key].map(item => ({
                                ...item,
                                _showHint: false
                            }));
                        }
                    }

                    this.questions = data.data;
                    this.hasResult = true;
                    this.allExpanded = true;
                    this.expanded = { general: true, behavioral: true, technical: true, company_specific: true };
                } catch (err) {
                    this.errorMessage = err.message || 'Terjadi kesalahan.';
                } finally {
                    this.isLoading = false;
                }
            },

            _buildPlainText() {
                const catLabels = {
                    general: 'PERTANYAAN UMUM (GENERAL)',
                    behavioral: 'BEHAVIORAL (STAR METHOD)',
                    technical: 'TEKNIS / FUNGSIONAL',
                    company_specific: 'SPESIFIK PERUSAHAAN'
                };

                let txt = `INTERVIEW PREP — ${this.form.job_title}`;
                if (this.form.company) txt += ` di ${this.form.company}`;
                txt += `\nLevel: ${this.form.level} | Fokus: ${this.form.focus}\n`;
                txt += '='.repeat(50) + '\n\n';

                for (const key of Object.keys(catLabels)) {
                    const items = this.questions[key];
                    if (!items || items.length === 0) continue;
                    txt += `${catLabels[key]}\n` + '-'.repeat(40) + '\n';
                    items.forEach((item, i) => {
                        txt += `${i + 1}. ${item.q}\n`;
                        if (item.hint) txt += `   💡 Tips: ${item.hint}\n`;
                        txt += '\n';
                    });
                    txt += '\n';
                }
                return txt;
            },

            copyAll() {
                const txt = this._buildPlainText();
                navigator.clipboard.writeText(txt).then(() => {
                    this.isCopied = true;
                    setTimeout(() => { this.isCopied = false; }, 2500);
                });
            },

            downloadTxt() {
                const txt = this._buildPlainText();
                const filename = `Interview-Prep-${(this.form.job_title || 'Posisi').replace(/[^a-zA-Z0-9]/g, '_')}.txt`;
                const blob = new Blob([txt], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            },

            printQuestions() {
                let printIframe = document.getElementById('printInterviewIframe');
                if (printIframe) printIframe.remove();

                printIframe = document.createElement('iframe');
                printIframe.id = 'printInterviewIframe';
                Object.assign(printIframe.style, { position: 'fixed', right: '0', bottom: '0', width: '0', height: '0', border: '0', visibility: 'hidden' });
                document.body.appendChild(printIframe);

                const esc = (s) => (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                const catLabels = {
                    general: 'Pertanyaan Umum (General)',
                    behavioral: 'Behavioral (STAR Method)',
                    technical: 'Teknis / Fungsional',
                    company_specific: 'Spesifik Perusahaan'
                };

                let sectionsHtml = '';
                for (const [key, label] of Object.entries(catLabels)) {
                    const items = this.questions[key];
                    if (!items || items.length === 0) continue;
                    sectionsHtml += `
                        <div class="section">
                            <div class="section-title">${esc(label)}</div>
                            <ol>
                                ${items.map(item => `
                                    <li>
                                        <strong>${esc(item.q)}</strong>
                                        ${item.hint ? `<div class="hint">💡 ${esc(item.hint)}</div>` : ''}
                                        <div class="answer-space"></div>
                                    </li>
                                `).join('')}
                            </ol>
                        </div>
                    `;
                }

                const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Interview Prep - ${esc(this.form.job_title)}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 20mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10.5pt; line-height: 1.5; color: #111; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 1.5pt solid #222; padding-bottom: 8px; margin-bottom: 16px; }
        .header h1 { font-size: 16pt; margin: 0 0 4px 0; }
        .header .sub { font-size: 10pt; color: #444; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 11pt; font-weight: bold; border-bottom: 1pt solid #666; padding-bottom: 2px; margin-bottom: 8px; }
        ol { padding-left: 20px; }
        li { margin-bottom: 12px; font-size: 10.5pt; }
        .hint { font-size: 9.5pt; color: #555; margin-top: 2px; font-style: italic; }
        .answer-space { border-bottom: 1px dotted #ccc; height: 32px; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Interview Prep</h1>
        <div class="sub">${esc(this.form.job_title)}${this.form.company ? ` &bull; ${esc(this.form.company)}` : ''}</div>
    </div>
    ${sectionsHtml}
</body>
</html>`;

                const doc = printIframe.contentDocument || printIframe.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();

                setTimeout(() => {
                    try {
                        printIframe.contentWindow.focus();
                        printIframe.contentWindow.print();
                    } catch (e) {
                        console.error('Print error:', e);
                    }
                }, 300);
            }
        }));
    };

    if (window.Alpine) {
        defineComponent();
    } else {
        document.addEventListener('alpine:init', defineComponent);
    }
})();
</script>
