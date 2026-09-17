<div class="py-8 sm:py-12 bg-[#fafaf9] dark:bg-[#0c0a09] min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
         x-data="fullCvBuilder({
            polishUrl: @js(route('career.cv-builder.polish')),
            csrfToken: @js(csrf_token()),
            jobPost: @js($jobPost ? [
                'id' => $jobPost->id,
                'title' => $jobPost->title,
                'company' => $jobPost->company_name ?: ($jobPost->job_company ?: 'Perusahaan'),
                'city' => trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $jobPost->city?->name ?? ''))
            ] : null),
         })"
         x-cloak>

        <!-- Top Header & Banner -->
        <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                        <span>AI ATS Resume Engine</span>
                    </span>
                    <template x-if="jobTarget.title">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                            <span>Target: <strong x-text="jobTarget.title"></strong></span>
                        </span>
                    </template>
                </div>
                <h1 class="font-serif-title text-2xl sm:text-3xl lg:text-4xl font-bold text-zinc-950 dark:text-zinc-50 mt-2.5">
                    ATS CV & Resume Builder
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1.5 max-w-2xl leading-relaxed">
                    Susun CV standar ATS yang rapi, profesional, dan siap lolos screening HRD. Pantau skor kelengkapan secara real-time dan unduh PDF siap lamar.
                </p>
            </div>

            <!-- Action Buttons Top Bar -->
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <button type="button" 
                        @click="resetToTemplate()" 
                        class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-zinc-200/80 hover:bg-zinc-300/80 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all active:scale-95">
                    <i class="fa-solid fa-arrow-rotate-left text-[11px]"></i>
                    <span>Muat Contoh</span>
                </button>
                <button type="button" 
                        @click="downloadTxt()" 
                        class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-sm transition-all active:scale-95">
                    <i class="fa-solid fa-download text-xs"></i>
                    <span>Unduh TXT</span>
                </button>
                <button type="button" 
                        @click="printCv()" 
                        class="cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/25 transition-all active:scale-95">
                    <i class="fa-solid fa-print text-xs"></i>
                    <span>Cetak / Unduh PDF</span>
                </button>
            </div>
        </div>

        <!-- Main Content 2-Column Split: Form Editor (Left) & Preview + Analysis (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT COLUMN: Form Sections (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Section 1: Data Diri -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center">1</span>
                        <div>
                            <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Data Diri</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Identitas dan saluran kontak utama Anda</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="cv.name" 
                                       @input="saveToStorage()"
                                       placeholder="Nama Lengkap" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Jabatan / Headline <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="cv.headline" 
                                       @input="saveToStorage()"
                                       placeholder="Frontend Engineer / Social Media Specialist" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       x-model="cv.email" 
                                       @input="saveToStorage()"
                                       placeholder="email@anda.com" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    No. HP <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="cv.phone" 
                                       @input="saveToStorage()"
                                       placeholder="08123456789" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Domisili
                                </label>
                                <input type="text" 
                                       x-model="cv.city" 
                                       @input="saveToStorage()"
                                       placeholder="Bandung, Indonesia" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Link & Portofolio
                                </label>
                                <span class="text-[10px] text-zinc-400">Satu link per baris (LinkedIn, Portofolio, GitHub)</span>
                            </div>
                            <textarea x-model="cv.links_text" 
                                      @input="saveToStorage()"
                                      rows="2" 
                                      placeholder="linkedin.com/in/username&#10;github.com/username&#10;portofolio.me" 
                                      class="w-full px-3.5 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Ringkasan Profil -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center">2</span>
                            <div>
                                <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Ringkasan Profil</h2>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Deskripsi singkat tentang pengalaman dan keunggulan Anda (2–4 kalimat)</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="aiPolishSummary()" 
                                :disabled="isPolishingSummary"
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[11px] font-bold border border-emerald-500/20 transition-all active:scale-95 disabled:opacity-50">
                            <i x-show="!isPolishingSummary" class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                            <i x-show="isPolishingSummary" class="fa-solid fa-circle-notch fa-spin text-[10px]"></i>
                            <span x-text="isPolishingSummary ? 'Memoles...' : 'AI Poles Ringkasan'"></span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        <textarea x-model="cv.summary" 
                                  @input="saveToStorage()"
                                  rows="4" 
                                  placeholder="Contoh: Frontend Developer dengan 3+ tahun pengalaman membangun web app modern berbasis React dan Vue. Terbiasa mengoptimalkan performa halaman hingga 40% dan berkolaborasi intensif dengan tim produk." 
                                  class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white leading-relaxed transition-all"></textarea>
                    </div>
                </div>

                <!-- Section 3: Pengalaman Kerja -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center">3</span>
                            <div>
                                <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Pengalaman Kerja</h2>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Riwayat karir, magang, atau proyek profesional</p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="addExperience()" 
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-900 text-[11px] font-bold transition-all active:scale-95">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Pengalaman</span>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <template x-for="(exp, index) in cv.experiences" :key="index">
                            <div class="p-4 sm:p-5 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 relative space-y-4">
                                <!-- Experience Actions (Move Up, Down, Delete) -->
                                <div class="flex items-center justify-between border-b border-zinc-200/60 dark:border-zinc-700/40 pb-3">
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-[10px] flex items-center justify-center font-bold" x-text="index + 1"></span>
                                        <span x-text="exp.position || 'Pengalaman Kerja Baru'"></span>
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                                @click="moveExperience(index, -1)" 
                                                :disabled="index === 0"
                                                title="Pindah Naik"
                                                class="cursor-pointer w-7 h-7 rounded-lg bg-zinc-200/70 dark:bg-zinc-700/70 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-600 dark:text-zinc-300 disabled:opacity-30 disabled:cursor-not-allowed text-xs flex items-center justify-center">
                                            <i class="fa-solid fa-chevron-up"></i>
                                        </button>
                                        <button type="button" 
                                                @click="moveExperience(index, 1)" 
                                                :disabled="index === cv.experiences.length - 1"
                                                title="Pindah Turun"
                                                class="cursor-pointer w-7 h-7 rounded-lg bg-zinc-200/70 dark:bg-zinc-700/70 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-600 dark:text-zinc-300 disabled:opacity-30 disabled:cursor-not-allowed text-xs flex items-center justify-center">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                        <button type="button" 
                                                @click="deleteExperience(index)" 
                                                title="Hapus"
                                                class="cursor-pointer w-7 h-7 rounded-lg bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-xs flex items-center justify-center ml-1">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Posisi</label>
                                        <input type="text" 
                                               x-model="exp.position" 
                                               @input="saveToStorage()"
                                               placeholder="Contoh: UI/UX Designer" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Perusahaan</label>
                                        <input type="text" 
                                               x-model="exp.company" 
                                               @input="saveToStorage()"
                                               placeholder="Contoh: PT Inovasi Maju" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Lokasi</label>
                                        <input type="text" 
                                               x-model="exp.location" 
                                               @input="saveToStorage()"
                                               placeholder="Jakarta / Remote" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Mulai</label>
                                        <input type="text" 
                                               x-model="exp.start" 
                                               @input="saveToStorage()"
                                               placeholder="Jan 2022" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Selesai</label>
                                        <input type="text" 
                                               x-model="exp.end" 
                                               @input="saveToStorage()"
                                               placeholder="Sekarang / Des 2023" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">
                                            Pencapaian / Tugas
                                        </label>
                                        <button type="button" 
                                                @click="aiPolishAchievements(index)" 
                                                class="cursor-pointer text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                            <i class="fa-solid fa-wand-magic-sparkles text-[9px]"></i>
                                            <span>AI Action-Verb Bullets</span>
                                        </button>
                                    </div>
                                    <span class="text-[10px] text-zinc-400 block -mt-1">Satu per baris, mulai dengan kata kerja aktif dan sertakan metrik terukur.</span>
                                    <textarea x-model="exp.achievements_text" 
                                              @input="saveToStorage()"
                                              rows="3" 
                                              placeholder="Merancang 15+ flow fitur baru yang meningkatkan user engagement sebesar 25%&#10;Memimpin redesign sistem navigasi aplikasi mobile&#10;Melakukan usability testing dengan 30 responden pengguna aktif" 
                                              class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white leading-relaxed"></textarea>
                                </div>
                            </div>
                        </template>

                        <div x-show="cv.experiences.length === 0" class="p-6 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 text-center text-xs text-zinc-400">
                            Belum ada riwayat pengalaman kerja. Klik "Tambah Pengalaman" di atas.
                        </div>
                    </div>
                </div>

                <!-- Section 4: Pendidikan -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center">4</span>
                            <div>
                                <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Pendidikan</h2>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Latar belakang pendidikan formal atau vokasi</p>
                            </div>
                        </div>

                        <button type="button" 
                                @click="addEducation()" 
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 hover:bg-zinc-800 dark:hover:bg-white text-white dark:text-zinc-900 text-[11px] font-bold transition-all active:scale-95">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Pendidikan</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(edu, index) in cv.education" :key="index">
                            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/80 dark:border-zinc-700/60 space-y-3">
                                <div class="flex items-center justify-between border-b border-zinc-200/60 dark:border-zinc-700/40 pb-2.5">
                                    <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-zinc-200 dark:bg-zinc-700 text-[10px] flex items-center justify-center font-bold" x-text="index + 1"></span>
                                        <span x-text="edu.institution || 'Institusi Pendidikan Baru'"></span>
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" 
                                                @click="moveEducation(index, -1)" 
                                                :disabled="index === 0"
                                                class="cursor-pointer w-6 h-6 rounded-lg bg-zinc-200/70 dark:bg-zinc-700/70 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-600 dark:text-zinc-300 disabled:opacity-30 disabled:cursor-not-allowed text-[11px] flex items-center justify-center">
                                            <i class="fa-solid fa-chevron-up"></i>
                                        </button>
                                        <button type="button" 
                                                @click="moveEducation(index, 1)" 
                                                :disabled="index === cv.education.length - 1"
                                                class="cursor-pointer w-6 h-6 rounded-lg bg-zinc-200/70 dark:bg-zinc-700/70 hover:bg-zinc-300 dark:hover:bg-zinc-600 text-zinc-600 dark:text-zinc-300 disabled:opacity-30 disabled:cursor-not-allowed text-[11px] flex items-center justify-center">
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </button>
                                        <button type="button" 
                                                @click="deleteEducation(index)" 
                                                class="cursor-pointer w-6 h-6 rounded-lg bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 text-[11px] flex items-center justify-center ml-1">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Sekolah / Kampus</label>
                                        <input type="text" 
                                               x-model="edu.institution" 
                                               @input="saveToStorage()"
                                               placeholder="Universitas Indonesia" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Jurusan / Gelar</label>
                                        <input type="text" 
                                               x-model="edu.degree" 
                                               @input="saveToStorage()"
                                               placeholder="S1 Ilmu Komunikasi" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Mulai</label>
                                        <input type="text" 
                                               x-model="edu.start" 
                                               @input="saveToStorage()"
                                               placeholder="2019" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Selesai</label>
                                        <input type="text" 
                                               x-model="edu.end" 
                                               @input="saveToStorage()"
                                               placeholder="2023" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-zinc-700 dark:text-zinc-300">Catatan</label>
                                        <input type="text" 
                                               x-model="edu.note" 
                                               @input="saveToStorage()"
                                               placeholder="IPK 3.85 / Cumlaude" 
                                               class="w-full px-3 py-2 text-xs rounded-lg bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="cv.education.length === 0" class="p-6 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 text-center text-xs text-zinc-400">
                            Belum ada riwayat pendidikan. Klik "Tambah Pendidikan" di atas.
                        </div>
                    </div>
                </div>

                <!-- Section 5: Keahlian & Lainnya -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 sm:p-6 shadow-xs">
                    <div class="flex items-center gap-3 pb-4 border-b border-zinc-100 dark:border-zinc-800/80 mb-5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center justify-center">5</span>
                        <div>
                            <h2 class="font-bold text-sm text-zinc-900 dark:text-zinc-100">Keahlian & Lainnya</h2>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Keterampilan teknis, bahasa, dan sertifikasi pendukung</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Keahlian Utama
                            </label>
                            <span class="text-[10px] text-zinc-400 block -mt-1">Pisahkan dengan koma (minimal 5 keahlian untuk ATS)</span>
                            <input type="text" 
                                   x-model="cv.skills_text" 
                                   @input="saveToStorage()"
                                   placeholder="Figma, Wireframing, React.js, Tailwind CSS, Usability Testing, Git" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Bahasa
                            </label>
                            <span class="text-[10px] text-zinc-400 block -mt-1">Pisahkan dengan koma</span>
                            <input type="text" 
                                   x-model="cv.languages_text" 
                                   @input="saveToStorage()"
                                   placeholder="Bahasa Indonesia (Penutur Asli), Bahasa Inggris (Profesional)" 
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Sertifikasi & Pelatihan
                            </label>
                            <span class="text-[10px] text-zinc-400 block -mt-1">Satu per baris</span>
                            <textarea x-model="cv.certifications_text" 
                                      @input="saveToStorage()"
                                      rows="3" 
                                      placeholder="Google UX Design Professional Certificate (2023)&#10;Certified Scrum Master (CSM)&#10;Pelatihan UI/UX Design Binar Academy" 
                                      class="w-full px-3.5 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-zinc-900 dark:text-white transition-all"></textarea>
                        </div>
                    </div>
                </div>

            </div>


            <!-- RIGHT COLUMN: Realtime Live ATS Preview & Analysis (5 Cols Sticky) -->
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                
                <!-- Ringkasan & Analisis CV Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-xs">
                    <div class="flex items-center justify-between pb-3.5 border-b border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="font-bold text-xs uppercase tracking-wider text-zinc-900 dark:text-zinc-100">
                                Ringkasan & Analisis CV
                            </h3>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold" :class="analysisScore >= 80 ? 'text-emerald-600 dark:text-emerald-400' : (analysisScore >= 50 ? 'text-amber-500' : 'text-zinc-400')">
                                <span x-text="completedCount"></span>/8 Selesai
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden my-3.5">
                        <div class="h-full transition-all duration-500"
                             :class="analysisScore >= 80 ? 'bg-emerald-500' : (analysisScore >= 50 ? 'bg-amber-500' : 'bg-zinc-400')"
                             :style="`width: ${analysisScore}%`"></div>
                    </div>

                    <!-- Checklist Items -->
                    <div class="space-y-3 pt-1 text-xs">
                        <template x-for="item in checklist" :key="item.id">
                            <div class="flex items-start gap-2.5">
                                <span class="shrink-0 mt-0.5" 
                                      :class="item.passed ? 'text-emerald-500' : 'text-zinc-300 dark:text-zinc-600'">
                                    <i :class="item.passed ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'"></i>
                                </span>
                                <div class="flex-1 min-w-0 leading-tight">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[10px] uppercase font-bold tracking-wider"
                                              :class="item.passed ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400'" 
                                              x-text="item.passed ? 'selesai:' : 'belum:'"></span>
                                        <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="item.title"></span>
                                    </div>
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5" x-text="item.desc"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Live Clean ATS Paper Preview -->
                <div class="bg-white rounded-2xl border border-zinc-200 shadow-xl overflow-hidden">
                    <div class="px-4 py-3 bg-zinc-100/80 border-b border-zinc-200 flex items-center justify-between text-xs">
                        <span class="font-bold text-zinc-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-eye text-zinc-400"></i>
                            <span>Pratinjau Langsung (A4 ATS)</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Standar ATS Helvetica/Arial</span>
                    </div>

                    <div class="p-6 text-zinc-900 font-sans text-xs space-y-3 max-h-[520px] overflow-y-auto custom-scrollbar">
                        <!-- ATS Header -->
                        <div class="text-center border-b border-zinc-800 pb-2.5">
                            <h2 class="text-base font-bold uppercase tracking-wider text-zinc-950" x-text="cv.name || 'NAMA LENGKAP'"></h2>
                            <p class="text-zinc-800 font-semibold text-xs mt-0.5" x-text="cv.headline || 'Jabatan / Headline Target'"></p>
                            <div class="flex flex-wrap items-center justify-center gap-x-2.5 gap-y-0.5 text-[10px] text-zinc-600 mt-1">
                                <span x-show="cv.city" x-text="cv.city"></span>
                                <span x-show="cv.phone" x-text="'• ' + cv.phone"></span>
                                <span x-show="cv.email" x-text="'• ' + cv.email"></span>
                            </div>
                            <div x-show="getLinksArray().length > 0" class="flex flex-wrap items-center justify-center gap-x-2 text-[10px] text-zinc-500 mt-0.5">
                                <template x-for="(lnk, idx) in getLinksArray()" :key="idx">
                                    <span x-text="(idx > 0 ? '• ' : '') + lnk"></span>
                                </template>
                            </div>
                        </div>

                        <!-- ATS Summary -->
                        <div x-show="cv.summary">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-300 pb-0.5 mb-1">
                                Ringkasan Profesional
                            </h3>
                            <p class="text-[11px] text-zinc-700 leading-relaxed text-justify" x-text="cv.summary"></p>
                        </div>

                        <!-- ATS Experience -->
                        <div x-show="cv.experiences.length > 0">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-300 pb-0.5 mb-1.5">
                                Pengalaman Kerja
                            </h3>
                            <div class="space-y-2.5">
                                <template x-for="(exp, idx) in cv.experiences" :key="idx">
                                    <div class="text-[11px]">
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-zinc-900" x-text="exp.position || 'Posisi'"></span>
                                            <span class="text-[10px] font-semibold text-zinc-500" x-text="[exp.start, exp.end].filter(Boolean).join(' - ')"></span>
                                        </div>
                                        <div class="text-zinc-600 font-medium text-[10.5px]">
                                            <span x-text="exp.company"></span>
                                            <span x-show="exp.location" x-text="' · ' + exp.location"></span>
                                        </div>
                                        <ul class="list-disc list-inside mt-0.5 space-y-0.5 text-zinc-700 text-[10.5px] leading-relaxed">
                                            <template x-for="(ach, aIdx) in getLines(exp.achievements_text)" :key="aIdx">
                                                <li x-text="ach"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ATS Education -->
                        <div x-show="cv.education.length > 0">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-300 pb-0.5 mb-1.5">
                                Pendidikan
                            </h3>
                            <div class="space-y-2">
                                <template x-for="(edu, idx) in cv.education" :key="idx">
                                    <div class="text-[11px]">
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-zinc-900" x-text="edu.institution || 'Sekolah / Kampus'"></span>
                                            <span class="text-[10px] font-semibold text-zinc-500" x-text="[edu.start, edu.end].filter(Boolean).join(' - ')"></span>
                                        </div>
                                        <div class="text-zinc-600 text-[10.5px]" x-text="edu.degree"></div>
                                        <div x-show="edu.note" class="text-[10px] text-zinc-500" x-text="edu.note"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ATS Skills, Languages & Certifications -->
                        <div x-show="cv.skills_text || cv.languages_text || cv.certifications_text" class="space-y-2 pt-1">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-300 pb-0.5 mb-1">
                                Keahlian & Lainnya
                            </h3>
                            <div x-show="cv.skills_text" class="text-[10.5px]">
                                <strong class="text-zinc-900">Keahlian:</strong> 
                                <span class="text-zinc-700" x-text="cv.skills_text"></span>
                            </div>
                            <div x-show="cv.languages_text" class="text-[10.5px]">
                                <strong class="text-zinc-900">Bahasa:</strong> 
                                <span class="text-zinc-700" x-text="cv.languages_text"></span>
                            </div>
                            <div x-show="cv.certifications_text" class="text-[10.5px]">
                                <strong class="text-zinc-900">Sertifikasi:</strong>
                                <ul class="list-disc list-inside mt-0.5 text-zinc-700">
                                    <template x-for="(cert, cIdx) in getLines(cv.certifications_text)" :key="cIdx">
                                        <li x-text="cert"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('fullCvBuilder', (config) => ({
        isPolishingSummary: false,
        jobTarget: config.jobPost || {},

        cv: {
            name: '',
            headline: config.jobPost?.title || '',
            email: '',
            phone: '',
            city: config.jobPost?.city || '',
            links_text: '',
            summary: '',
            experiences: [],
            education: [],
            skills_text: '',
            languages_text: '',
            certifications_text: ''
        },

        init() {
            try {
                const saved = localStorage.getItem('disekitar_full_cv_data');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    this.cv = Object.assign({}, this.cv, parsed);
                } else {
                    this.resetToTemplate();
                }
            } catch (e) {
                this.resetToTemplate();
            }
        },

        saveToStorage() {
            try {
                localStorage.setItem('disekitar_full_cv_data', JSON.stringify(this.cv));
            } catch (e) {}
        },

        resetToTemplate() {
            this.cv = {
                name: 'Budi Pratama',
                headline: this.jobTarget.title || 'Product Marketing & Growth Specialist',
                email: 'budi.pratama@gmail.com',
                phone: '081234567890',
                city: this.jobTarget.city || 'Bandung, Jawa Barat',
                links_text: 'linkedin.com/in/budipratama\nportfolio.budipratama.com',
                summary: 'Product Marketer dengan 4+ tahun pengalaman mengelola akuisisi pengguna dan kampanye multichannel berbasis data. Berhasil meningkatkan conversion rate sebesar 32% dan memimpin strategi peluncuran 3 produk digital nasional.',
                experiences: [
                    {
                        position: 'Senior Growth Marketing Associate',
                        company: 'PT Digital Solusi Nusantara',
                        location: 'Jakarta (Hybrid)',
                        start: 'Jan 2022',
                        end: 'Sekarang',
                        achievements_text: 'Merancang strategi kampanye performa digital yang meningkatkan akuisisi pengguna baru sebesar 45% dalam 6 bulan.\nMemimpin optimasi landing page dan funnel pendaftaran hingga menurunkan bounce rate 28%.\nMengelola budget promosi Rp 200 juta/bulan dengan efisiensi ROI meningkat 1.8x.'
                    },
                    {
                        position: 'Marketing & Campaign Specialist',
                        company: 'Kreatif Media Group',
                        location: 'Bandung',
                        start: 'Agu 2019',
                        end: 'Des 2021',
                        achievements_text: 'Mengeksekusi lebih dari 40 kampanye pemasaran terintegrasi dengan total jangkauan 2+ juta audiens.\nBerkolaborasi dengan tim data untuk memetakan retensi dan cohort pengguna aktif bulanan.'
                    }
                ],
                education: [
                    {
                        institution: 'Universitas Padjadjaran',
                        degree: 'S1 Manajemen Bisnis & Pemasaran',
                        start: '2015',
                        end: '2019',
                        note: 'IPK 3.82 / Lulus dengan Pujian (Cumlaude)'
                    }
                ],
                skills_text: 'Growth Hacking, Performance Marketing, Funnel Optimization, Google Analytics 4, Meta Ads, SQL Dasar, Data-driven Storytelling',
                languages_text: 'Bahasa Indonesia (Penutur Asli), Bahasa Inggris (Profesional)',
                certifications_text: 'Google Analytics Individual Qualification (2023)\nReforge Growth Series Certificate'
            };
            this.saveToStorage();
        },

        getLines(str) {
            if (!str) return [];
            return str.split('\n').map(s => s.trim()).filter(Boolean);
        },

        getLinksArray() {
            return this.getLines(this.cv.links_text);
        },

        addExperience() {
            this.cv.experiences.push({
                position: '',
                company: '',
                location: '',
                start: '',
                end: '',
                achievements_text: ''
            });
            this.saveToStorage();
        },

        deleteExperience(index) {
            this.cv.experiences.splice(index, 1);
            this.saveToStorage();
        },

        moveExperience(index, direction) {
            const target = index + direction;
            if (target < 0 || target >= this.cv.experiences.length) return;
            const temp = this.cv.experiences[index];
            this.cv.experiences[index] = this.cv.experiences[target];
            this.cv.experiences[target] = temp;
            this.saveToStorage();
        },

        addEducation() {
            this.cv.education.push({
                institution: '',
                degree: '',
                start: '',
                end: '',
                note: ''
            });
            this.saveToStorage();
        },

        deleteEducation(index) {
            this.cv.education.splice(index, 1);
            this.saveToStorage();
        },

        moveEducation(index, direction) {
            const target = index + direction;
            if (target < 0 || target >= this.cv.education.length) return;
            const temp = this.cv.education[index];
            this.cv.education[index] = this.cv.education[target];
            this.cv.education[target] = temp;
            this.saveToStorage();
        },

        // Realtime Analysis Checklist
        get checklist() {
            const cv = this.cv;
            const hasNameAndHeadline = Boolean(cv.name?.trim() && cv.headline?.trim());
            const hasEmailAndPhone = Boolean(cv.email?.trim() && cv.phone?.trim());
            
            // Sentence count approx
            const sentences = (cv.summary || '').split(/[.!?]+/).filter(s => s.trim().length > 5);
            const hasGoodSummary = sentences.length >= 2;

            const hasMinOneExp = (cv.experiences || []).length >= 1;
            
            // 2+ points per exp
            const hasGoodPointsPerExp = hasMinOneExp && cv.experiences.every(e => {
                const lines = (e.achievements_text || '').split('\n').filter(l => l.trim().length > 3);
                return lines.length >= 2;
            });

            // Metrics/numbers in points
            const allPoints = (cv.experiences || []).flatMap(e => (e.achievements_text || '').split('\n'));
            const hasNumbersInPoints = allPoints.some(pt => /\d+[%kKmM]?|\b(juta|ribu|kali|jam|hari)\b/i.test(pt));

            const hasEducation = (cv.education || []).length >= 1 && Boolean(cv.education[0]?.institution?.trim());

            // 5+ skills
            const skills = (cv.skills_text || '').split(',').map(s => s.trim()).filter(Boolean);
            const has5Skills = skills.length >= 5;

            return [
                {
                    id: 1,
                    title: 'nama & jabatan',
                    desc: 'isi nama lengkap dan jabatan yang kamu tuju.',
                    passed: hasNameAndHeadline
                },
                {
                    id: 2,
                    title: 'email & no. hp',
                    desc: 'recruiter butuh keduanya untuk menghubungi kamu.',
                    passed: hasEmailAndPhone
                },
                {
                    id: 3,
                    title: 'ringkasan 2–4 kalimat',
                    desc: 'tulis 2–4 kalimat: siapa kamu, keahlian utama, dan yang kamu cari.',
                    passed: hasGoodSummary
                },
                {
                    id: 4,
                    title: 'minimal 1 pengalaman',
                    desc: 'tambahkan pengalaman kerja, magang, freelance, atau organisasi.',
                    passed: hasMinOneExp
                },
                {
                    id: 5,
                    title: '2+ poin per pengalaman',
                    desc: 'tiap pengalaman sebaiknya punya 2–4 poin pencapaian.',
                    passed: hasGoodPointsPerExp
                },
                {
                    id: 6,
                    title: 'pencapaian pakai angka',
                    desc: 'tambahkan angka: %, jumlah pengguna, target, waktu yang dihemat.',
                    passed: hasNumbersInPoints
                },
                {
                    id: 7,
                    title: 'pendidikan',
                    desc: 'isi pendidikan terakhir kamu.',
                    passed: hasEducation
                },
                {
                    id: 8,
                    title: '5+ keahlian',
                    desc: 'cantumkan minimal 5 keahlian yang relevan dengan posisi.',
                    passed: has5Skills
                }
            ];
        },

        get completedCount() {
            return this.checklist.filter(i => i.passed).length;
        },

        get analysisScore() {
            return Math.round((this.completedCount / this.checklist.length) * 100);
        },

        async aiPolishSummary() {
            this.isPolishingSummary = true;
            try {
                const res = await fetch(config.polishUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        section: 'summary',
                        language: 'id',
                        data: {
                            name: this.cv.name,
                            headline: this.cv.headline,
                            skills: this.cv.skills_text,
                            draft: this.cv.summary
                        }
                    })
                });

                const data = await res.json();
                if (data.success && data.result) {
                    this.cv.summary = data.result;
                    this.saveToStorage();
                }
            } catch (err) {
                console.error(err);
            } finally {
                this.isPolishingSummary = false;
            }
        },

        async aiPolishAchievements(index) {
            const exp = this.cv.experiences[index];
            if (!exp) return;

            try {
                const res = await fetch(config.polishUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        section: 'achievement',
                        language: 'id',
                        data: {
                            position: exp.position,
                            company: exp.company,
                            draft: exp.achievements_text
                        }
                    })
                });

                const data = await res.json();
                if (data.success && data.result) {
                    exp.achievements_text = data.result;
                    this.saveToStorage();
                }
            } catch (err) {
                console.error(err);
            }
        },

        downloadTxt() {
            const filename = `CV-${(this.cv.name || 'Pelamar').replace(/[^a-zA-Z0-9]/g, '_')}.txt`;
            let content = `${this.cv.name}\n`;
            content += `${this.cv.headline}\n`;
            content += `${[this.cv.city, this.cv.phone, this.cv.email].filter(Boolean).join(' | ')}\n`;
            if (this.cv.links_text) content += `${this.cv.links_text}\n`;
            content += `\nRINGKASAN PROFESIONAL\n${this.cv.summary}\n\n`;

            if (this.cv.experiences.length > 0) {
                content += `PENGALAMAN KERJA\n`;
                this.cv.experiences.forEach(exp => {
                    content += `${exp.position} - ${exp.company} (${[exp.start, exp.end].filter(Boolean).join(' - ')})\n`;
                    if (exp.location) content += `Lokasi: ${exp.location}\n`;
                    this.getLines(exp.achievements_text).forEach(pt => {
                        content += `- ${pt}\n`;
                    });
                    content += `\n`;
                });
            }

            if (this.cv.education.length > 0) {
                content += `PENDIDIKAN\n`;
                this.cv.education.forEach(edu => {
                    content += `${edu.institution} - ${edu.degree} (${[edu.start, edu.end].filter(Boolean).join(' - ')})\n`;
                    if (edu.note) content += `Catatan: ${edu.note}\n`;
                    content += `\n`;
                });
            }

            if (this.cv.skills_text) {
                content += `KEAHLIAN UTAMA\n${this.cv.skills_text}\n\n`;
            }
            if (this.cv.languages_text) {
                content += `BAHASA\n${this.cv.languages_text}\n\n`;
            }
            if (this.cv.certifications_text) {
                content += `SERTIFIKASI & PELATIHAN\n${this.cv.certifications_text}\n\n`;
            }

            const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        printCv() {
            let printIframe = document.getElementById('printFullCvIframe');
            if (printIframe) printIframe.remove();

            printIframe = document.createElement('iframe');
            printIframe.id = 'printFullCvIframe';
            printIframe.style.position = 'fixed';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            printIframe.style.width = '0';
            printIframe.style.height = '0';
            printIframe.style.border = '0';
            printIframe.style.visibility = 'hidden';
            document.body.appendChild(printIframe);

            const esc = (str) => (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            let expHtml = '';
            if (this.cv.experiences.length > 0) {
                expHtml = `
                    <div class="section">
                        <div class="section-title">PENGALAMAN KERJA</div>
                        ${this.cv.experiences.map(e => `
                            <div class="item">
                                <div class="item-header">
                                    <span class="role">${esc(e.position)}</span>
                                    <span class="period">${esc([e.start, e.end].filter(Boolean).join(' - '))}</span>
                                </div>
                                <div class="company">${esc(e.company)}${e.location ? ` &bull; ${esc(e.location)}` : ''}</div>
                                <ul class="points">
                                    ${this.getLines(e.achievements_text).map(p => `<li>${esc(p)}</li>`).join('')}
                                </ul>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            let eduHtml = '';
            if (this.cv.education.length > 0) {
                eduHtml = `
                    <div class="section">
                        <div class="section-title">PENDIDIKAN</div>
                        ${this.cv.education.map(ed => `
                            <div class="item">
                                <div class="item-header">
                                    <span class="role">${esc(ed.institution)}</span>
                                    <span class="period">${esc([ed.start, ed.end].filter(Boolean).join(' - '))}</span>
                                </div>
                                <div class="company">${esc(ed.degree)}</div>
                                ${ed.note ? `<div class="note">${esc(ed.note)}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            let othersHtml = '';
            if (this.cv.skills_text || this.cv.languages_text || this.cv.certifications_text) {
                othersHtml = `
                    <div class="section">
                        <div class="section-title">KEAHLIAN & LAINNYA</div>
                        ${this.cv.skills_text ? `<div class="meta-row"><strong>Keahlian:</strong> ${esc(this.cv.skills_text)}</div>` : ''}
                        ${this.cv.languages_text ? `<div class="meta-row"><strong>Bahasa:</strong> ${esc(this.cv.languages_text)}</div>` : ''}
                        ${this.cv.certifications_text ? `
                            <div class="meta-row"><strong>Sertifikasi:</strong>
                                <ul class="points">
                                    ${this.getLines(this.cv.certifications_text).map(c => `<li>${esc(c)}</li>`).join('')}
                                </ul>
                            </div>` : ''}
                    </div>
                `;
            }

            const linksJoined = this.getLinksArray().map(esc).join(' &bull; ');

            const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CV ATS - ${esc(this.cv.name)}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 1.5pt solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 3px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .headline {
            font-size: 11pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 4px 0;
        }
        .contact {
            font-size: 9.5pt;
            color: #444;
        }
        .links {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }
        .section {
            margin-bottom: 13px;
        }
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1pt solid #444;
            padding-bottom: 2px;
            margin-bottom: 6px;
            color: #111;
        }
        .summary-text {
            text-align: justify;
            font-size: 10pt;
            margin: 0;
        }
        .item { margin-bottom: 8px; }
        .item-header {
            display: flex;
            justify-content: space-between;
            font-size: 10.5pt;
        }
        .role { font-weight: bold; color: #111; }
        .period { font-size: 9.5pt; font-weight: bold; color: #444; }
        .company { font-size: 10pt; font-style: italic; color: #333; margin-bottom: 2px; }
        .note { font-size: 9.5pt; color: #555; }
        .points {
            margin: 0;
            padding-left: 18px;
            font-size: 9.8pt;
        }
        .points li {
            margin-bottom: 2px;
            text-align: justify;
        }
        .meta-row {
            font-size: 10pt;
            margin-bottom: 4px;
        }
        @media print {
            body { background: #fff; color: #000; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>${esc(this.cv.name)}</h1>
        <div class="headline">${esc(this.cv.headline)}</div>
        <div class="contact">
            ${[esc(this.cv.city), esc(this.cv.phone), esc(this.cv.email)].filter(Boolean).join(' &bull; ')}
        </div>
        ${linksJoined ? `<div class="links">${linksJoined}</div>` : ''}
    </div>

    ${this.cv.summary ? `
        <div class="section">
            <div class="section-title">RINGKASAN PROFESIONAL</div>
            <p class="summary-text">${esc(this.cv.summary)}</p>
        </div>` : ''}

    ${expHtml}
    ${eduHtml}
    ${othersHtml}
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
});
</script>
