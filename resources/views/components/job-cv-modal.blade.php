@props([
    'post',
])

@php
    $companyName = $post->company_name ?: ($post->job_company ?: 'Perusahaan');
    $cityName = $post->city?->name ?? 'Indonesia';
    $cleanCity = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $cityName));
@endphp

<div x-data="jobCvModal({
    postId: {{ $post->id }},
    jobTitle: @js($post->title),
    company: @js($companyName),
    city: @js($cleanCity),
    generateUrl: @js(route('job.cv.generate')),
    csrfToken: @js(csrf_token()),
})" 
     x-cloak
     class="relative">

    <!-- Modal Trigger Card / Button -->
    <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800/60 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/20 dark:to-teal-950/20 p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
            <!-- Icon -->
            <div class="shrink-0 w-14 h-14 rounded-2xl bg-emerald-600 dark:bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-600/30">
                <i class="fa-solid fa-file-lines text-white text-2xl"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100">ATS CV & Resume Builder</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>AI Powered</span>
                    </span>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Buat CV profesional yang lolos screening ATS untuk posisi <strong class="text-emerald-700 dark:text-emerald-400">{{ $post->title }}</strong> di {{ $companyName }}. Dengan analisis real-time dan AI polish.
                </p>
            </div>

            <!-- CTA Button -->
            <div class="shrink-0 w-full sm:w-auto">
                <a href="{{ route('career.cv-builder', ['job' => $post->id]) }}" 
                   wire:navigate
                   class="cursor-pointer block w-full sm:w-auto text-center px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-sm shadow-md shadow-emerald-600/25 transition-all hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-arrow-right mr-2"></i>
                    <span>Buka CV Builder</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Dialog -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">

        <div @click.away="closeModal()" 
             class="relative w-full max-w-2xl mx-auto bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-4.5 border-b border-zinc-200/80 dark:border-zinc-800/80 flex items-center justify-between bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200/70 dark:border-emerald-800/60 flex items-center justify-center shrink-0 text-sm shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M9 14h6"></path>
                            <path d="M9 18h6"></path>
                            <path d="M9 10h6"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-50 tracking-tight">AI ATS CV & Resume Maker</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Standar ATS</span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-[280px] sm:max-w-md mt-0.5">
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $post->title }}</span> · {{ $companyName }}
                        </p>
                    </div>
                </div>
                <button type="button" 
                        @click="closeModal()" 
                        class="cursor-pointer w-8 h-8 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400 text-zinc-400 transition-colors flex items-center justify-center border border-zinc-200/60 dark:border-zinc-700/60">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-6 flex-1 custom-scrollbar">
                
                <!-- View 1: Input Form -->
                <div x-show="!hasResult" class="space-y-5">
                    <!-- Helper Banner -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg bg-emerald-600/10 dark:bg-emerald-400/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 text-xs">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                            Lengkapi profil dasar Anda. AI akan meracik resume dengan kata kunci yang ramah sistem penyaring ATS khusus posisi <strong class="text-emerald-600 dark:text-emerald-400">{{ $post->title }}</strong> di {{ $companyName }}.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <!-- Identitas Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.name" 
                                       placeholder="Contoh: Budi Pratama, S.Kom"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Kota Domisili
                                </label>
                                <input type="text" 
                                       x-model="form.city" 
                                       placeholder="Contoh: Bandung, Jawa Barat"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                        </div>

                        <!-- Kontak Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    No. HP / WA
                                </label>
                                <input type="text" 
                                       x-model="form.phone" 
                                       placeholder="081234567890"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Alamat Email
                                </label>
                                <input type="email" 
                                       x-model="form.email" 
                                       placeholder="email.anda@gmail.com"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    LinkedIn / Portofolio URL
                                </label>
                                <input type="text" 
                                       x-model="form.linkedin" 
                                       placeholder="linkedin.com/in/profil"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                        </div>

                        <!-- Target Posisi & Bahasa -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Target Posisi
                                </label>
                                <input type="text" 
                                       x-model="form.target_title" 
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Bahasa CV / Resume
                                </label>
                                <select x-model="form.language"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white">
                                    <option value="id">Bahasa Indonesia (Standar Nasional)</option>
                                    <option value="en">English (International ATS)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Riwayat Pengalaman Kerja / Proyek -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Riwayat Pengalaman Kerja / Organisasi / Proyek
                                </label>
                                <span class="text-[10px] text-zinc-400">Opsional namun sangat dianjurkan</span>
                            </div>
                            <textarea x-model="form.experience" 
                                      rows="3" 
                                      placeholder="Sebutkan posisi, nama kantor/organisasi, tahun, dan tugas utama Anda. Contoh: Staff Admin di PT Maju (2022-2024), mengurus pengarsipan data, rekap invoice bulanan, dan koordinasi pengiriman."
                                      class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400"></textarea>
                        </div>

                        <!-- Riwayat Pendidikan & Keterampilan -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Pendidikan Terakhir
                                </label>
                                <input type="text" 
                                       x-model="form.education" 
                                       placeholder="Contoh: S1 Manajemen - Universitas Padjadjaran (2019 - 2023)"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Keahlian / Skill Utama
                                </label>
                                <input type="text" 
                                       x-model="form.skills" 
                                       placeholder="Contoh: Excel, Negosiasi, Data Entry, Photoshop"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                        </div>

                        <div x-show="errorMessage" class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <!-- View 2: Generated Result Preview -->
                <div x-show="hasResult" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Preview CV ATS (Siap Cetak A4 / PDF)
                        </span>
                        <span class="text-[10px] px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold border border-emerald-500/20">
                            Format ATS Standar
                        </span>
                    </div>

                    <!-- Live Clean ATS Paper Preview -->
                    <div class="p-5 sm:p-7 rounded-2xl bg-white text-zinc-900 border border-zinc-200 shadow-inner font-sans text-xs space-y-4 max-h-[500px] overflow-y-auto">
                        <!-- Header -->
                        <div class="text-center border-b border-zinc-300 pb-3">
                            <h2 class="text-base sm:text-lg font-bold uppercase tracking-wider text-zinc-950" x-text="cvData.name || form.name"></h2>
                            <p class="text-zinc-700 font-semibold text-xs mt-0.5" x-text="cvData.target_title || form.target_title"></p>
                            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-[11px] text-zinc-600 mt-1.5">
                                <span x-show="cvData.city" x-text="cvData.city"></span>
                                <span x-show="cvData.phone" x-text="'• ' + cvData.phone"></span>
                                <span x-show="cvData.email" x-text="'• ' + cvData.email"></span>
                                <span x-show="cvData.linkedin" x-text="'• ' + cvData.linkedin"></span>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-200 pb-1 mb-1.5">
                                Ringkasan Profesional
                            </h3>
                            <p class="text-zinc-700 leading-relaxed text-justify" x-text="cvData.summary"></p>
                        </div>

                        <!-- Skills -->
                        <div x-show="cvData.skills && cvData.skills.length > 0">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-200 pb-1 mb-1.5">
                                Keahlian Utama (Keywords)
                            </h3>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <template x-for="(skill, idx) in cvData.skills" :key="idx">
                                    <span class="px-2 py-0.5 bg-zinc-100 text-zinc-800 rounded border border-zinc-300 text-[10px] font-medium" x-text="skill"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Experience -->
                        <div x-show="cvData.experiences && cvData.experiences.length > 0">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-200 pb-1 mb-2">
                                Pengalaman Kerja
                            </h3>
                            <div class="space-y-3">
                                <template x-for="(exp, idx) in cvData.experiences" :key="idx">
                                    <div>
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-zinc-900" x-text="exp.role"></span>
                                            <span class="text-[10px] font-semibold text-zinc-500" x-text="exp.period"></span>
                                        </div>
                                        <div class="text-[11px] text-zinc-600 font-medium" x-text="exp.company"></div>
                                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-zinc-700 text-[11px] leading-relaxed">
                                            <template x-for="(pt, pIdx) in exp.points" :key="pIdx">
                                                <li x-text="pt"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Education -->
                        <div x-show="cvData.education && cvData.education.length > 0">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 border-b border-zinc-200 pb-1 mb-2">
                                Pendidikan
                            </h3>
                            <div class="space-y-2">
                                <template x-for="(edu, idx) in cvData.education" :key="idx">
                                    <div>
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-zinc-900" x-text="edu.degree"></span>
                                            <span class="text-[10px] font-semibold text-zinc-500" x-text="edu.period"></span>
                                        </div>
                                        <div class="text-[11px] text-zinc-600" x-text="edu.institution"></div>
                                        <p x-show="edu.description" class="text-[10px] text-zinc-500 mt-0.5" x-text="edu.description"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                        <i class="fa-regular fa-lightbulb text-amber-500"></i>
                        <span>Gunakan tombol <strong>Cetak / PDF</strong> untuk langsung menyimpan file PDF siap lamar dengan format satu lembar A4 ATS.</span>
                    </p>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-zinc-200/80 dark:border-zinc-800/80 bg-zinc-50/90 dark:bg-zinc-900/90 backdrop-blur-xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <template x-if="hasResult">
                        <button type="button" 
                                @click="hasResult = false" 
                                class="cursor-pointer inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all active:scale-95">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Atur Ulang Data</span>
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-3">
                    <template x-if="!hasResult">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    @click="closeModal()" 
                                    class="group cursor-pointer inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white text-xs font-bold shadow-md shadow-rose-600/20 border border-rose-500 transition-all active:scale-95">
                                <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-[10px]">
                                    <i class="fa-solid fa-xmark"></i>
                                </span>
                                <span>Batal</span>
                            </button>
                            <button type="button" 
                                    @click="generateCv()" 
                                    :disabled="isLoading"
                                    class="group cursor-pointer inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-600/25 border border-emerald-500 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-[10px]">
                                    <i x-show="!isLoading" class="fa-solid fa-wand-magic-sparkles"></i>
                                    <i x-show="isLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                                </span>
                                <span x-text="isLoading ? 'Menyusun CV ATS...' : 'Generate CV ATS'"></span>
                            </button>
                        </div>
                    </template>

                    <template x-if="hasResult">
                        <div class="flex items-center gap-2.5">
                            <button type="button" 
                                    @click="downloadTxt()" 
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold border border-amber-500 shadow-sm transition-all active:scale-95">
                                <i class="fa-solid fa-download"></i>
                                <span>Unduh (.txt)</span>
                            </button>
                            <button type="button" 
                                    @click="printCv()" 
                                    class="cursor-pointer inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold border border-emerald-500 shadow-md shadow-emerald-600/20 transition-all active:scale-95">
                                <i class="fa-solid fa-print"></i>
                                <span>Cetak / PDF</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('jobCvModal', (config) => ({
        isOpen: false,
        isLoading: false,
        hasResult: false,
        errorMessage: '',
        cvData: {
            name: '',
            city: '',
            phone: '',
            email: '',
            linkedin: '',
            target_title: '',
            summary: '',
            skills: [],
            experiences: [],
            education: []
        },
        form: {
            name: '',
            city: config.city || '',
            phone: '',
            email: '',
            linkedin: '',
            target_title: config.jobTitle || '',
            experience: '',
            education: '',
            skills: '',
            language: 'id'
        },

        init() {
            try {
                const saved = localStorage.getItem('disekitar_applicant_profile');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    if (parsed.name) this.form.name = parsed.name;
                    if (parsed.city) this.form.city = parsed.city;
                    if (parsed.phone) this.form.phone = parsed.phone;
                    if (parsed.email) this.form.email = parsed.email;
                    if (parsed.linkedin) this.form.linkedin = parsed.linkedin;
                    if (parsed.experience) this.form.experience = parsed.experience;
                    if (parsed.education) this.form.education = parsed.education;
                    if (parsed.skills) this.form.skills = parsed.skills;
                }
            } catch (e) {
                // local storage fail-safe
            }
        },

        openModal() {
            this.isOpen = true;
            this.errorMessage = '';
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        async generateCv() {
            if (!this.form.name.trim()) {
                this.errorMessage = 'Nama lengkap wajib diisi.';
                return;
            }

            this.isLoading = true;
            this.errorMessage = '';

            try {
                // Simpan profil ke localStorage
                localStorage.setItem('disekitar_applicant_profile', JSON.stringify({
                    name: this.form.name,
                    city: this.form.city,
                    phone: this.form.phone,
                    email: this.form.email,
                    linkedin: this.form.linkedin,
                    experience: this.form.experience,
                    education: this.form.education,
                    skills: this.form.skills,
                }));

                const response = await fetch(config.generateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: config.postId,
                        name: this.form.name,
                        city: this.form.city,
                        phone: this.form.phone,
                        email: this.form.email,
                        linkedin: this.form.linkedin,
                        target_title: this.form.target_title,
                        experience: this.form.experience,
                        education: this.form.education,
                        skills: this.form.skills,
                        language: this.form.language,
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menyusun CV. Silakan coba lagi.');
                }

                this.cvData = data.data;
                this.hasResult = true;
            } catch (err) {
                this.errorMessage = err.message || 'Terjadi kesalahan saat menyusun CV.';
            } finally {
                this.isLoading = false;
            }
        },

        downloadTxt() {
            if (!this.cvData) return;
            const filename = `CV-ATS-${(this.cvData.name || 'Pelamar').replace(/[^a-zA-Z0-9]/g, '_')}.txt`;
            
            let content = `${this.cvData.name}\n`;
            content += `${this.cvData.target_title}\n`;
            content += `${[this.cvData.city, this.cvData.phone, this.cvData.email, this.cvData.linkedin].filter(Boolean).join(' | ')}\n\n`;
            content += `RINGKASAN PROFESIONAL\n${this.cvData.summary}\n\n`;
            
            if (this.cvData.skills && this.cvData.skills.length > 0) {
                content += `KEAHLIAN UTAMA\n${this.cvData.skills.join(', ')}\n\n`;
            }

            if (this.cvData.experiences && this.cvData.experiences.length > 0) {
                content += `PENGALAMAN KERJA\n`;
                this.cvData.experiences.forEach(exp => {
                    content += `${exp.role} - ${exp.company} (${exp.period})\n`;
                    (exp.points || []).forEach(pt => {
                        content += `- ${pt}\n`;
                    });
                    content += `\n`;
                });
            }

            if (this.cvData.education && this.cvData.education.length > 0) {
                content += `PENDIDIKAN\n`;
                this.cvData.education.forEach(edu => {
                    content += `${edu.degree} - ${edu.institution} (${edu.period})\n`;
                    if (edu.description) content += `  ${edu.description}\n`;
                });
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
            if (!this.cvData) return;

            let printIframe = document.getElementById('printCvIframe');
            if (printIframe) {
                printIframe.remove();
            }

            printIframe = document.createElement('iframe');
            printIframe.id = 'printCvIframe';
            printIframe.style.position = 'fixed';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            printIframe.style.width = '0';
            printIframe.style.height = '0';
            printIframe.style.border = '0';
            printIframe.style.visibility = 'hidden';
            document.body.appendChild(printIframe);

            const escapeHtml = (str) => (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            let skillsHtml = '';
            if (this.cvData.skills && this.cvData.skills.length > 0) {
                skillsHtml = `
                    <div class="section">
                        <div class="section-title">KEAHLIAN UTAMA</div>
                        <div class="skills-list">${this.cvData.skills.map(s => `<span>${escapeHtml(s)}</span>`).join(', ')}</div>
                    </div>
                `;
            }

            let expHtml = '';
            if (this.cvData.experiences && this.cvData.experiences.length > 0) {
                expHtml = `
                    <div class="section">
                        <div class="section-title">PENGALAMAN KERJA</div>
                        ${this.cvData.experiences.map(e => `
                            <div class="item">
                                <div class="item-header">
                                    <span class="role">${escapeHtml(e.role)}</span>
                                    <span class="period">${escapeHtml(e.period)}</span>
                                </div>
                                <div class="company">${escapeHtml(e.company)}</div>
                                <ul class="points">
                                    ${(e.points || []).map(p => `<li>${escapeHtml(p)}</li>`).join('')}
                                </ul>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            let eduHtml = '';
            if (this.cvData.education && this.cvData.education.length > 0) {
                eduHtml = `
                    <div class="section">
                        <div class="section-title">PENDIDIKAN</div>
                        ${this.cvData.education.map(ed => `
                            <div class="item">
                                <div class="item-header">
                                    <span class="role">${escapeHtml(ed.degree)}</span>
                                    <span class="period">${escapeHtml(ed.period)}</span>
                                </div>
                                <div class="company">${escapeHtml(ed.institution)}</div>
                                ${ed.description ? `<div class="edu-desc">${escapeHtml(ed.description)}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            const htmlContent = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CV ATS - ${escapeHtml(this.cvData.name)}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        * {
            box-sizing: border-box;
        }
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
        .target-title {
            font-size: 11pt;
            font-weight: bold;
            color: #222;
            margin: 0 0 4px 0;
        }
        .contact-info {
            font-size: 9.5pt;
            color: #444;
        }
        .section {
            margin-bottom: 14px;
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
        .skills-list {
            font-size: 10pt;
            line-height: 1.5;
        }
        .item {
            margin-bottom: 8px;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            font-size: 10.5pt;
        }
        .role {
            font-weight: bold;
            color: #111;
        }
        .period {
            font-size: 9.5pt;
            font-weight: bold;
            color: #444;
        }
        .company {
            font-size: 10pt;
            font-style: italic;
            color: #333;
            margin-bottom: 3px;
        }
        .points {
            margin: 0;
            padding-left: 18px;
            font-size: 9.8pt;
        }
        .points li {
            margin-bottom: 2px;
            text-align: justify;
        }
        .edu-desc {
            font-size: 9.5pt;
            color: #555;
        }
        @media print {
            body {
                background: #fff;
                color: #000;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>${escapeHtml(this.cvData.name)}</h1>
        <div class="target-title">${escapeHtml(this.cvData.target_title)}</div>
        <div class="contact-info">
            ${[escapeHtml(this.cvData.city), escapeHtml(this.cvData.phone), escapeHtml(this.cvData.email), escapeHtml(this.cvData.linkedin)].filter(Boolean).join(' &bull; ')}
        </div>
    </div>

    <div class="section">
        <div class="section-title">RINGKASAN PROFESIONAL</div>
        <p class="summary-text">${escapeHtml(this.cvData.summary)}</p>
    </div>

    ${skillsHtml}
    ${expHtml}
    ${eduHtml}
</body>
</html>`;

            const doc = printIframe.contentDocument || printIframe.contentWindow.document;
            doc.open();
            doc.write(htmlContent);
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
