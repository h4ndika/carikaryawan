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
    <div class="rounded-2xl border border-indigo-200 dark:border-indigo-800/60 bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/20 dark:to-blue-950/20 p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
            <!-- Icon -->
            <div class="shrink-0 w-14 h-14 rounded-2xl bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center shadow-lg shadow-indigo-600/30">
                <i class="fa-solid fa-file-pen text-white text-2xl"></i>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <h3 class="font-bold text-base text-zinc-900 dark:text-zinc-100">Buat Surat Lamaran Instan (Cover Letter)</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600 text-white">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>AI Powered</span>
                    </span>
                </div>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                    Generate surat lamaran profesional khusus posisi <strong class="text-indigo-700 dark:text-indigo-400">{{ $post->title }}</strong> di {{ $companyName }}. Sesuaikan tone dan bahasa sesuai kebutuhan.
                </p>
            </div>

            <!-- CTA Button -->
            <div class="shrink-0 w-full sm:w-auto">
                <button type="button" 
                        @click="openModal()" 
                        class="cursor-pointer block w-full sm:w-auto text-center px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm shadow-md shadow-indigo-600/25 transition-all hover:scale-105 active:scale-95">
                    <i class="fa-solid fa-arrow-right mr-2"></i>
                    <span>Buat Surat Lamaran</span>
                </button>
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
             class="relative w-full max-w-xl mx-auto bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-4.5 border-b border-zinc-200/80 dark:border-zinc-800/80 flex items-center justify-between bg-zinc-50/80 dark:bg-zinc-900/80 backdrop-blur-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/70 dark:border-indigo-800/60 flex items-center justify-center shrink-0 text-sm shadow-xs">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm text-zinc-900 dark:text-zinc-50 tracking-tight">AI Cover Letter Generator</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">1-Click ATS</span>
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
                    <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 flex items-start gap-3">
                        <div class="w-6 h-6 rounded-lg bg-indigo-600/10 dark:bg-indigo-400/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0 mt-0.5 text-xs">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                            Lengkapi profil singkat Anda. AI akan menyelaraskan kualifikasi Anda dengan posisi <strong class="text-indigo-600 dark:text-indigo-400">{{ $post->title }}</strong> di {{ $companyName }}.
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
                                       placeholder="Nama Anda"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Kota Domisili Surat
                                </label>
                                <input type="text" 
                                       x-model="form.city" 
                                       placeholder="Contoh: Bandung, Jakarta"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                        </div>

                        <!-- Kontak Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    No. WhatsApp / HP
                                </label>
                                <input type="text" 
                                       x-model="form.phone" 
                                       placeholder="081234567890"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Alamat Email
                                </label>
                                <input type="email" 
                                       x-model="form.email" 
                                       placeholder="email.anda@gmail.com"
                                       class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                            </div>
                        </div>

                        <!-- Opsi Bahasa & Tone Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Bahasa Surat
                                </label>
                                <select x-model="form.language"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white">
                                    <option value="id">Bahasa Indonesia (Formal & Santun)</option>
                                    <option value="en">English (Professional & ATS)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                    Gaya Bahasa (Tone)
                                </label>
                                <select x-model="form.tone"
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white">
                                    <option value="professional">Profesional & Berbobot (Standar HR)</option>
                                    <option value="enthusiastic">Penuh Semangat & Antusias</option>
                                    <option value="concise">Singkat, Padat & Langsung Inti</option>
                                </select>
                            </div>
                        </div>

                        <!-- Pengalaman Singkat -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Latar Belakang / Pengalaman Singkat
                            </label>
                            <textarea x-model="form.experience" 
                                      rows="2" 
                                      placeholder="Contoh: Fresh graduate S1 Manajemen / 2 tahun pengalaman sebagai staf operasional & customer service di bidang retail."
                                      class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400"></textarea>
                        </div>

                        <!-- Keterampilan -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                Keterampilan / Skill Unggulan
                            </label>
                            <input type="text" 
                                   x-model="form.skills" 
                                   placeholder="Contoh: Microsoft Excel, komunikasi persuasif, manajemen waktu"
                                   class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-zinc-50/80 dark:bg-zinc-800/70 border border-zinc-200 dark:border-zinc-700/80 focus:bg-white dark:focus:bg-zinc-800 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-zinc-900 dark:text-white placeholder:text-zinc-400">
                        </div>

                        <div x-show="errorMessage" class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs flex items-center gap-2.5">
                            <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                            <span x-text="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <!-- View 2: Generated Result -->
                <div x-show="hasResult" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Draf Surat Lamaran Kerja
                        </span>
                        <span class="text-[10px] px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold border border-emerald-500/20">
                            Siap Digunakan
                        </span>
                    </div>

                    <textarea x-model="resultText" 
                              rows="14" 
                              class="w-full p-4.5 font-sans text-xs sm:text-sm leading-relaxed rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-300/80 dark:border-zinc-700/80 focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 focus:outline-hidden selection:bg-indigo-500 selection:text-white shadow-inner"></textarea>
                    
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                        <i class="fa-regular fa-lightbulb text-amber-500"></i>
                        <span>Anda dapat langsung mengedit teks di atas sesuai kebutuhan sebelum menyalin atau mengunduhnya.</span>
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
                                    @click="generateLetter()" 
                                    :disabled="isLoading"
                                    class="group cursor-pointer inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-bold shadow-md shadow-indigo-600/25 border border-indigo-500 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center text-[10px]">
                                    <i x-show="!isLoading" class="fa-solid fa-wand-magic-sparkles"></i>
                                    <i x-show="isLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                                </span>
                                <span x-text="isLoading ? 'Menyusun Surat Lamaran...' : 'Generate Surat Lamaran'"></span>
                            </button>
                        </div>
                    </template>

                    <template x-if="hasResult">
                        <div class="flex items-center gap-2.5">
                            <button type="button" 
                                    @click="copyToClipboard()" 
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold border border-emerald-500 shadow-sm transition-all active:scale-95">
                                <i :class="isCopied ? 'fa-solid fa-check' : 'fa-regular fa-copy'"></i>
                                <span x-text="isCopied ? 'Tersalin!' : 'Salin Teks'"></span>
                            </button>
                            <button type="button" 
                                    @click="downloadTxt()" 
                                    class="cursor-pointer inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold border border-amber-500 shadow-sm transition-all active:scale-95">
                                <i class="fa-solid fa-download"></i>
                                <span>Unduh (.txt)</span>
                            </button>
                            <button type="button" 
                                    @click="printLetter()" 
                                    class="cursor-pointer inline-flex items-center gap-2 px-4.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold border border-indigo-500 shadow-md shadow-indigo-600/20 transition-all active:scale-95">
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

                let letter = data.cover_letter || '';
                // Bersihkan jika ada tag think tersisa
                letter = letter.replace(/<think\b[^>]*>[\s\S]*?<\/think>/gi, '');
                letter = letter.replace(/^[\s\S]*?<\/think>\s*/gi, '');
                letter = letter.replace(/^```[a-zA-Z]*\s*/i, '').replace(/\s*```$/, '');
                this.resultText = letter.trim();
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

        downloadTxt() {
            if (!this.resultText) return;
            const filename = `Surat-Lamaran-${(this.form.name || 'Pelamar').replace(/[^a-zA-Z0-9]/g, '_')}.txt`;
            const blob = new Blob([this.resultText], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        printLetter() {
            if (!this.resultText) return;

            // Buat atau bersihkan iframe tersembunyi untuk proses cetak
            let printIframe = document.getElementById('printCoverLetterIframe');
            if (printIframe) {
                printIframe.remove();
            }

            printIframe = document.createElement('iframe');
            printIframe.id = 'printCoverLetterIframe';
            printIframe.style.position = 'fixed';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            printIframe.style.width = '0';
            printIframe.style.height = '0';
            printIframe.style.border = '0';
            printIframe.style.visibility = 'hidden';
            document.body.appendChild(printIframe);

            const safeParagraphs = this.resultText
                .split(/\n\s*\n/)
                .filter(p => p.trim().length > 0)
                .map(p => {
                    const clean = p
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/\n/g, '<br>');
                    return `<p>${clean}</p>`;
                })
                .join('');

            const htmlContent = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Lamaran - ${config.jobTitle}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25mm 25mm 20mm 25mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: "Times New Roman", Times, "Liberation Serif", serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #111;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        p {
            margin: 0 0 1.1em 0;
            text-align: justify;
            text-justify: inter-word;
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
    <div class="letter-content">${safeParagraphs}</div>
</body>
</html>`;

            const doc = printIframe.contentWindow || printIframe.contentDocument;
            const iframeDoc = printIframe.contentDocument || printIframe.contentWindow.document;

            iframeDoc.open();
            iframeDoc.write(htmlContent);
            iframeDoc.close();

            setTimeout(() => {
                try {
                    printIframe.contentWindow.focus();
                    printIframe.contentWindow.print();
                } catch (e) {
                    console.error('Print error:', e);
                }
            }, 250);
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
