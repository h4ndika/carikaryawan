@props([
    'post',
])

@php
    $widgetPostUrl = route('widget.post', ['slug' => $post->slug]);
    $widgetBadgeUrl = route('widget.badge', ['slug' => $post->slug]);
    $iframePostCode = '<iframe src="' . $widgetPostUrl . '" width="100%" height="460" style="max-width:380px;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;" frameborder="0" loading="lazy"></iframe>';
    $iframeBadgeCode = '<iframe src="' . $widgetBadgeUrl . '" width="280" height="70" style="border:none;overflow:hidden;" frameborder="0" loading="lazy"></iframe>';
@endphp

<div x-data="embedWidgetModal({
    postCode: @js($iframePostCode),
    badgeCode: @js($iframeBadgeCode)
})" 
     x-cloak
     class="relative inline-block">

    <!-- Trigger Button -->
    <button type="button" 
            @click="openModal()" 
            class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700 text-xs font-semibold transition-all shadow-2xs hover:scale-105 active:scale-95"
            title="Sematkan Lencana atau Kartu Ulasan di Website Anda">
        <i class="fa-solid fa-code text-amber-600 dark:text-amber-400 text-xs"></i>
        <span>Sematkan Widget</span>
    </button>

    <!-- Modal Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="closeModal()" 
             class="relative w-full max-w-xl bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-zinc-950 dark:text-zinc-50">Sematkan di Website Anda</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Tampilkan ulasan kurasi resmi DISEKITAR di blog atau situs resmi bisnis Anda</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="cursor-pointer w-8 h-8 rounded-full bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="px-5 pt-3 border-b border-zinc-200 dark:border-zinc-800 flex items-center gap-4 text-xs font-semibold">
                <button type="button" 
                        @click="activeTab = 'card'" 
                        class="cursor-pointer pb-2.5 border-b-2 transition-colors"
                        :class="activeTab === 'card' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300'">
                    <i class="fa-regular fa-id-card mr-1"></i> Kartu Ulasan Lengkap
                </button>
                <button type="button" 
                        @click="activeTab = 'badge'" 
                        class="cursor-pointer pb-2.5 border-b-2 transition-colors"
                        :class="activeTab === 'badge' ? 'border-amber-500 text-amber-600 dark:text-amber-400' : 'border-transparent text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300'">
                    <i class="fa-solid fa-certificate mr-1"></i> Lencana "Featured on DISEKITAR"
                </button>
            </div>

            <!-- Tab Contents & Live Preview -->
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                
                <!-- Live Preview Frame Container -->
                <div class="p-4 rounded-xl bg-zinc-100 dark:bg-zinc-950 flex flex-col items-center justify-center border border-zinc-200/80 dark:border-zinc-800">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-500 mb-3">Live Preview:</span>
                    
                    <template x-if="activeTab === 'card'">
                        <div class="w-full flex justify-center">
                            <iframe src="{{ $widgetPostUrl }}" 
                                    class="w-full max-w-[360px] h-[450px] rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm bg-white" 
                                    frameborder="0"></iframe>
                        </div>
                    </template>

                    <template x-if="activeTab === 'badge'">
                        <div class="w-full flex justify-center py-4">
                            <iframe src="{{ $widgetBadgeUrl }}" 
                                    class="w-[280px] h-[70px] border-none overflow-hidden" 
                                    frameborder="0"></iframe>
                        </div>
                    </template>
                </div>

                <!-- Code Snippet Box -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">Salin Kode HTML:</span>
                        <span class="text-[11px] text-zinc-400">Tempel di WordPress, Webflow, atau HTML</span>
                    </div>

                    <div class="relative">
                        <textarea readonly 
                                  rows="3" 
                                  x-model="activeTab === 'card' ? postCode : badgeCode"
                                  class="w-full p-3 font-mono text-[11px] leading-relaxed bg-zinc-950 text-zinc-200 rounded-xl border border-zinc-800 focus:outline-none focus:ring-1 focus:ring-amber-500 select-all"></textarea>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Action -->
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/60 flex items-center justify-between">
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                    <span>Tautan resmi & aman (dofollow backlink)</span>
                </span>
                
                <button type="button" 
                        @click="copyCode()" 
                        class="cursor-pointer px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold text-xs inline-flex items-center gap-2 transition-transform active:scale-95 shadow-sm">
                    <i :class="copied ? 'fa-solid fa-check text-emerald-950' : 'fa-regular fa-copy'"></i>
                    <span x-text="copied ? 'Berhasil Disalin!' : 'Salin Kode HTML'"></span>
                </button>
            </div>

        </div>
    </div>
</div>

<script>
(() => {
    const defineComponent = () => {
        if (!window.Alpine) return;
        Alpine.data('embedWidgetModal', (config) => ({
            isOpen: false,
            activeTab: 'card',
            postCode: config.postCode,
            badgeCode: config.badgeCode,
            copied: false,

            openModal() {
                this.isOpen = true;
                this.copied = false;
            },

            closeModal() {
                this.isOpen = false;
            },

            async copyCode() {
                const textToCopy = this.activeTab === 'card' ? this.postCode : this.badgeCode;
                try {
                    await navigator.clipboard.writeText(textToCopy);
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2500);
                } catch (err) {
                    // Fallback
                    const ta = document.createElement('textarea');
                    ta.value = textToCopy;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2500);
                }
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
