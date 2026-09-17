@props([
    'post',
])

@php
    $companyName = $post->company_name ?: ($post->job_company ?: 'Perusahaan');
    $cityName = $post->city?->name ?? 'Indonesia';
    $cleanCity = trim(preg_replace('/^(KOTA|KAB\.?|KABUPATEN)\s+/i', '', $cityName));
@endphp

<div x-data="jobCoverLetterModal({
    postId: {{ $post->id }},
    jobTitle: @js($post->title),
    company: @js($companyName),
    city: @js($cleanCity),
    generateUrl: @js(route('job.cover-letter.generate')),
    csrfToken: @js(csrf_token()),
})" 
     x-cloak
     class="relative">

    <!-- Modal Trigger Banner / Button -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-900 via-zinc-900 to-zinc-950 p-5 sm:p-6 text-white border border-indigo-500/30 shadow-lg">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/3 -mb-8 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-300 text-lg shrink-0 mt-0.5">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Fitur Karir AI</span>
                        <span class="bg-indigo-500/30 text-indigo-200 text-[10px] font-bold px-2 py-0.5 rounded-full">1-Click</span>
                    </div>
                    <h3 class="font-serif-title text-base sm:text-lg font-bold text-white mt-0.5">
                        Buat Surat Lamaran Instan (Cover Letter)
                    </h3>
                    <p class="text-xs text-zinc-300 mt-1 max-w-xl leading-relaxed">
                        Generate surat lamaran kerja terstruktur otomatis khusus posisi <span class="text-indigo-200 font-semibold">{{ $post->title }}</span> di <span class="text-indigo-200 font-semibold">{{ $companyName }}</span>.
                    </p>
                </div>
            </div>

            <button type="button" 
                    @click="openModal()" 
                    class="cursor-pointer shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold tracking-wide transition-all shadow-md hover:scale-[1.02] active:scale-95">
                <i class="fa-solid fa-file-pen text-sm"></i>
                <span>Buat Surat Lamaran</span>
            </button>
        </div>
    </div>

    <!-- Modal Dialog -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">

        <div @click.away="closeModal()" 
             class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[92vh]">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between bg-zinc-50 dark:bg-zinc-900/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-zinc-950 dark:text-zinc-50">1-Click AI Cover Letter Generator</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                            Khusus untuk: <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $post->title }}</span> · {{ $companyName }}
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="cursor-pointer w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 overflow-y-auto space-y-4 flex-1">
                
                <!-- View 1: Input Form -->
                <div x-show="!hasResult">
                    <p class="text-xs text-zinc-600 dark:text-zinc-300 mb-4 leading-relaxed">
                        Lengkapi informasi dasar Anda di bawah ini agar AI dapat menyusun surat lamaran yang persuasif, relevan, dan sesuai standar HRD:
                    </p>

                    <div class="space-y-3.5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.name" 
                                       placeholder="Contoh: Rian Pratama"
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Kota Asal / Domisili Surat
                                </label>
                                <input type="text" 
                                       x-model="form.city" 
                                       placeholder="Contoh: Bandung, Jakarta, Yogyakarta"
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    No. WhatsApp / HP
                                </label>
                                <input type="text" 
                                       x-model="form.phone" 
                                       placeholder="Contoh: 081234567890"
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Alamat Email
                                </label>
                                <input type="email" 
                                       x-model="form.email" 
                                       placeholder="Contoh: rian.pratama@gmail.com"
                                       class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Bahasa Surat
                                </label>
                                <select x-model="form.language"
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                                    <option value="id">Bahasa Indonesia (Formal & Sopan)</option>
                                    <option value="en">English (Professional & ATS Friendly)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                    Gaya Bahasa (Tone)
                                </label>
                                <select x-model="form.tone"
                                        class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                                    <option value="professional">Profesional & Santun (Standar)</option>
                                    <option value="enthusiastic">Antusias & Energik</option>
                                    <option value="concise">Singkat, Padat & Ringkas</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Latar Belakang / Pengalaman Singkat
                            </label>
                            <textarea x-model="form.experience" 
                                      rows="2" 
                                      placeholder="Contoh: Pengalaman 2 tahun sebagai admin operasional & customer service di bidang retail / Fresh graduate S1 Manajemen."
                                      class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Keterampilan / Keahlian Utama yang Ingin Ditegaskan
                            </label>
                            <input type="text" 
                                   x-model="form.skills" 
                                   placeholder="Contoh: Microsoft Excel tingkat lanjut, komunikasi persuasif, manajemen stok barang"
                                   class="w-full px-3 py-2 text-xs rounded-xl bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-zinc-900 dark:text-white">
                        </div>

                        <div x-show="errorMessage" class="p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span x-text="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <!-- View 2: Generated Result -->
                <div x-show="hasResult" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-file-lines text-indigo-600 dark:text-indigo-400"></i>
                            Draf Surat Lamaran (Dapat Diedit Langsung):
                        </span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold">
                            Siap Digunakan
                        </span>
                    </div>

                    <textarea x-model="resultText" 
                              rows="14" 
                              class="w-full p-4 font-sans text-xs sm:text-sm leading-relaxed rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-300 dark:border-zinc-700 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden selection:bg-indigo-500 selection:text-white shadow-inner"></textarea>
                    
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 italic">
                        Tip: Anda dapat langsung mengedit teks di atas sesuai kebutuhan sebelum menyalin atau mengunduhnya.
                    </p>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3.5 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <template x-if="hasResult">
                        <button type="button" 
                                @click="hasResult = false" 
                                class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">
                            <i class="fa-solid fa-arrow-left text-[11px]"></i>
                            <span>Atur Ulang Data</span>
                        </button>
                    </template>
                </div>

                <div class="flex items-center gap-2">
                    <template x-if="!hasResult">
                        <button type="button" 
                                @click="generateLetter()" 
                                :disabled="isLoading"
                                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-xs font-bold tracking-wide transition-all shadow-md">
                            <i x-show="!isLoading" class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                            <i x-show="isLoading" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            <span x-text="isLoading ? 'Menyusun Surat Lamaran...' : 'Generate Surat Lamaran'"></span>
                        </button>
                    </template>

                    <template x-if="hasResult">
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click="copyToClipboard()" 
                                    class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-semibold transition-all">
                                <i :class="isCopied ? 'fa-solid fa-check text-emerald-500' : 'fa-regular fa-copy'"></i>
                                <span x-text="isCopied ? 'Tersalin!' : 'Salin Teks'"></span>
                            </button>
                            <button type="button" 
                                    @click="printLetter()" 
                                    class="cursor-pointer inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all shadow-md">
                                <i class="fa-solid fa-print"></i>
                                <span>Cetak / Simpan PDF</span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
(() => {
    const defineComponent = () => {
        if (!window.Alpine) return;
        if (window.Alpine.data('jobCoverLetterModal')) return;

        window.Alpine.data('jobCoverLetterModal', (config) => ({
            isOpen: false,
            isLoading: false,
            hasResult: false,
            isCopied: false,
            errorMessage: '',
            resultText: '',
            form: {
                name: '',
                city: config.city || '',
                phone: '',
                email: '',
                experience: '',
                skills: '',
                language: 'id',
                tone: 'professional'
            },

        init() {
            try {
                const saved = localStorage.getItem('disekitar_applicant_profile');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    this.form.name = parsed.name || '';
                    this.form.city = parsed.city || this.form.city;
                    this.form.phone = parsed.phone || '';
                    this.form.email = parsed.email || '';
                    this.form.experience = parsed.experience || '';
                    this.form.skills = parsed.skills || '';
                }
            } catch (e) {}

            window.addEventListener('open-cover-letter-modal', () => {
                this.openModal();
            });
        },

        openModal() {
            this.isOpen = true;
            this.errorMessage = '';
        },

        closeModal() {
            this.isOpen = false;
        },

        async generateLetter() {
            if (!this.form.name.trim()) {
                this.errorMessage = 'Silakan isi Nama Lengkap terlebih dahulu.';
                return;
            }

            this.errorMessage = '';
            this.isLoading = true;

            try {
                // Simpan profil pelamar ke localStorage agar tidak perlu ngetik ulang di loker berikutnya
                localStorage.setItem('disekitar_applicant_profile', JSON.stringify({
                    name: this.form.name,
                    city: this.form.city,
                    phone: this.form.phone,
                    email: this.form.email,
                    experience: this.form.experience,
                    skills: this.form.skills,
                }));

                const response = await fetch(config.generateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        post_id: config.postId,
                        name: this.form.name,
                        city: this.form.city,
                        phone: this.form.phone,
                        email: this.form.email,
                        experience: this.form.experience,
                        skills: this.form.skills,
                        language: this.form.language,
                        tone: this.form.tone,
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal membuat surat lamaran. Silakan coba lagi.');
                }

                this.resultText = data.cover_letter;
                this.hasResult = true;
            } catch (err) {
                this.errorMessage = err.message || 'Terjadi gangguan jaringan atau server.';
            } finally {
                this.isLoading = false;
            }
        },

        copyToClipboard() {
            if (!this.resultText) return;
            navigator.clipboard.writeText(this.resultText).then(() => {
                this.isCopied = true;
                setTimeout(() => { this.isCopied = false; }, 2500);
            });
        },

        printLetter() {
            if (!this.resultText) return;
            const printWindow = window.open('', '_blank');
            const safeText = this.resultText
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Surat Lamaran - ${config.jobTitle}</title>
                    <style>
                        @page { size: A4; margin: 2.5cm 2cm; }
                        body {
                            font-family: "Times New Roman", Times, serif;
                            font-size: 12pt;
                            line-height: 1.6;
                            color: #111;
                            margin: 0;
                            padding: 2cm;
                        }
                        p { margin-bottom: 1em; text-align: justify; }
                        @media print {
                            body { padding: 0; }
                        }
                    </style>
                </head>
                <body>
                    <div>${safeText}</div>
                    <script>
                        window.onload = function() {
                            window.print();
                        }
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
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
