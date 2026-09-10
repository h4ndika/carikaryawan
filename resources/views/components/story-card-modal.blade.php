@props([
    'post',
])

@php
    $storyImg = $post->primary_image_url ?: asset('images/placeholder.svg');
    $ratingVal = $post->google_rating ?? $post->rating ?? 4.8;
    $ratingCount = $post->google_reviews_count ?? $post->reviews_count ?? 120;
    $cityName = $post->city?->name ?? 'Indonesia';
    $provinceName = $post->province?->name ?? '';
    $categoryName = $post->category?->name ?? 'Tempat';
    $excerptClean = str($post->excerpt ?: strip_tags($post->content))->limit(120)->toString();
@endphp

<div x-data="storyCardGenerator({
    title: @js($post->title),
    category: @js($categoryName),
    city: @js($cityName),
    province: @js($provinceName),
    rating: @js($ratingVal),
    reviewsCount: @js($ratingCount),
    imageUrl: @js($storyImg),
    excerpt: @js($excerptClean),
    url: @js(url()->current()),
    slug: @js($post->slug)
})" 
     x-cloak
     class="relative inline-block">

    <!-- Trigger Button -->
    <button type="button" 
            @click="openModal()" 
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gradient-to-r from-pink-500/10 via-purple-500/10 to-amber-500/10 hover:from-pink-500/20 hover:to-amber-500/20 text-zinc-800 dark:text-zinc-200 border border-purple-200/60 dark:border-purple-800/60 text-xs font-semibold transition-all shadow-2xs hover:scale-105 active:scale-95"
            title="Bagikan ke Instagram Story / WhatsApp Status (9:16)">
        <i class="fa-brands fa-instagram text-pink-600 dark:text-pink-400 text-sm"></i>
        <span>Story 9:16</span>
    </button>

    <!-- Modal Backdrop & Dialog -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">

        <div @click.away="closeModal()" 
             class="relative w-full max-w-lg bg-zinc-900 text-white rounded-2xl border border-zinc-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            
            <!-- Header Modal -->
            <div class="px-5 py-4 border-b border-zinc-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-pink-500 to-amber-500 flex items-center justify-center text-white text-xs font-bold">
                        <i class="fa-brands fa-instagram"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-zinc-100">Kartu Story Media Sosial (9:16)</h3>
                        <p class="text-[11px] text-zinc-400">Siap diunduh untuk Instagram Story & WA Status</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" class="w-8 h-8 rounded-full bg-zinc-800 hover:bg-zinc-700 text-zinc-400 hover:text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Canvas Preview Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-4 flex flex-col items-center justify-center bg-zinc-950/60">
                <div class="relative max-w-[280px] sm:max-w-[320px] aspect-[9/16] rounded-xl overflow-hidden shadow-2xl border border-zinc-800">
                    <canvas x-ref="canvas" width="1080" height="1920" class="w-full h-full object-contain block"></canvas>
                    
                    <!-- Loading Indicator overlay while rendering image -->
                    <div x-show="rendering" class="absolute inset-0 bg-black/70 backdrop-blur-xs flex flex-col items-center justify-center gap-2 text-zinc-300 text-xs">
                        <i class="fa-solid fa-spinner fa-spin text-amber-500 text-2xl"></i>
                        <span>Menyiapkan gambar kartu...</span>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="p-4 border-t border-zinc-800 bg-zinc-900 flex flex-wrap items-center justify-between gap-2">
                <div class="text-[11px] text-zinc-400">
                    Format: <span class="font-bold text-zinc-200">1080×1920 HD</span> · Tanpa Beban Server
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="downloadStoryImage()" 
                            class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold text-xs inline-flex items-center gap-2 transition-transform active:scale-95 shadow-md">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Gambar</span>
                    </button>
                    <button type="button" 
                            @click="shareStory()" 
                            class="px-4 py-2 rounded-xl bg-zinc-800 hover:bg-zinc-700 text-zinc-200 font-semibold text-xs inline-flex items-center gap-1.5 transition-colors">
                        <i class="fa-solid fa-share-nodes"></i>
                        <span>Bagikan</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('storyCardGenerator', (config) => ({
        isOpen: false,
        rendering: false,
        canvasDataUrl: null,

        openModal() {
            this.isOpen = true;
            this.$nextTick(() => {
                this.renderCanvas();
            });
        },

        closeModal() {
            this.isOpen = false;
        },

        renderCanvas() {
            this.rendering = true;
            const canvas = this.$refs.canvas;
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            // 1. Base Gradient Background (Deep warm dark slate)
            const bgGradient = ctx.createLinearGradient(0, 0, 0, 1920);
            bgGradient.addColorStop(0, '#090d16');
            bgGradient.addColorStop(0.4, '#0f172a');
            bgGradient.addColorStop(1, '#020617');
            ctx.fillStyle = bgGradient;
            ctx.fillRect(0, 0, 1080, 1920);

            // Subtle decorative ambient glow at top and bottom
            const radialGlow = ctx.createRadialGradient(540, 300, 50, 540, 300, 600);
            radialGlow.addColorStop(0, 'rgba(217, 119, 6, 0.15)');
            radialGlow.addColorStop(1, 'rgba(217, 119, 6, 0)');
            ctx.fillStyle = radialGlow;
            ctx.fillRect(0, 0, 1080, 800);

            // Top Decorative Accent Bar
            const topBar = ctx.createLinearGradient(0, 0, 1080, 0);
            topBar.addColorStop(0, '#f59e0b');
            topBar.addColorStop(0.5, '#ec4899');
            topBar.addColorStop(1, '#8b5cf6');
            ctx.fillStyle = topBar;
            ctx.fillRect(0, 0, 1080, 16);

            // 2. Brand Header (DISEKITAR)
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 36px -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", Roboto, sans-serif';
            ctx.letterSpacing = '6px';
            ctx.textAlign = 'center';
            ctx.fillText('D I S E K I T A R', 540, 120);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '500 22px -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", sans-serif';
            ctx.letterSpacing = '3px';
            ctx.fillText('REKOMENDASI & KURASI LOKAL', 540, 160);

            // 3. Load Main Photo
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = config.imageUrl;

            img.onload = () => {
                this.drawCardContent(ctx, img);
                this.finishRendering(canvas);
            };

            img.onerror = () => {
                // Fallback jika gambar CORS error, gambar solid placeholder
                this.drawCardContent(ctx, null);
                this.finishRendering(canvas);
            };
        },

        drawCardContent(ctx, img) {
            // Container Photo (Card): 920px width, 880px height
            const imgX = 80;
            const imgY = 220;
            const imgW = 920;
            const imgH = 860;
            const radius = 32;

            ctx.save();
            this.roundRect(ctx, imgX, imgY, imgW, imgH, radius);
            ctx.clip();

            if (img) {
                // Draw image cover
                const hRatio = imgW / img.width;
                const vRatio = imgH / img.height;
                const ratio = Math.max(hRatio, vRatio);
                const centerShiftX = (imgW - img.width * ratio) / 2;
                const centerShiftY = (imgH - img.height * ratio) / 2;

                ctx.drawImage(img, 0, 0, img.width, img.height,
                    imgX + centerShiftX, imgY + centerShiftY, img.width * ratio, img.height * ratio);
            } else {
                ctx.fillStyle = '#1e293b';
                ctx.fillRect(imgX, imgY, imgW, imgH);
            }

            // Vignette shadow on bottom of photo
            const imgShadow = ctx.createLinearGradient(0, imgY + imgH - 300, 0, imgY + imgH);
            imgShadow.addColorStop(0, 'rgba(0,0,0,0)');
            imgShadow.addColorStop(1, 'rgba(0,0,0,0.85)');
            ctx.fillStyle = imgShadow;
            ctx.fillRect(imgX, imgY + imgH - 300, imgW, 300);

            // Category & City on bottom of photo
            ctx.fillStyle = '#f59e0b';
            ctx.font = 'bold 26px sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(config.category.toUpperCase(), imgX + 40, imgY + imgH - 60);

            ctx.fillStyle = '#ffffff';
            ctx.font = '600 24px sans-serif';
            ctx.fillText('📍 ' + config.city + (config.province ? ', ' + config.province : ''), imgX + 40, imgY + imgH - 24);

            ctx.restore();

            // Border on Photo
            ctx.save();
            this.roundRect(ctx, imgX, imgY, imgW, imgH, radius);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
            ctx.lineWidth = 3;
            ctx.stroke();
            ctx.restore();

            // 4. Rating Badge & Info
            const ratingY = 1140;
            ctx.fillStyle = '#fbbf24';
            ctx.font = 'bold 36px sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText('★ ' + Number(config.rating).toFixed(1), 80, ratingY);

            ctx.fillStyle = '#94a3b8';
            ctx.font = '500 24px sans-serif';
            ctx.fillText('(' + config.reviewsCount + '+ ulasan kurasi)', 200, ratingY);

            // 5. Post Title (Large, wrap text)
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 54px -apple-system, "Plus Jakarta Sans", serif';
            this.wrapText(ctx, config.title, 80, 1220, 920, 68, 2);

            // 6. Editorial Quote Box (Dark card with warm accent)
            const quoteY = 1380;
            const quoteH = 260;
            ctx.save();
            this.roundRect(ctx, 80, quoteY, 920, quoteH, 24);
            ctx.fillStyle = 'rgba(30, 41, 59, 0.7)';
            ctx.fill();
            ctx.strokeStyle = 'rgba(245, 158, 11, 0.3)';
            ctx.lineWidth = 2;
            ctx.stroke();
            ctx.restore();

            ctx.fillStyle = '#fcd34d';
            ctx.font = 'bold 22px sans-serif';
            ctx.fillText('❝ CATATAN KURASI REDAKSI', 120, quoteY + 60);

            ctx.fillStyle = '#e2e8f0';
            ctx.font = 'normal 30px sans-serif';
            this.wrapText(ctx, config.excerpt, 120, quoteY + 120, 840, 44, 3);

            // 7. Footer Call To Action
            const footerY = 1720;
            ctx.save();
            this.roundRect(ctx, 80, footerY, 920, 120, 60);
            const btnGrad = ctx.createLinearGradient(80, 0, 1000, 0);
            btnGrad.addColorStop(0, '#d97706');
            btnGrad.addColorStop(1, '#b45309');
            ctx.fillStyle = btnGrad;
            ctx.fill();
            ctx.restore();

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 30px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText('🔗 Baca Panduan Lengkap di disekitar.id', 540, footerY + 72);
        },

        finishRendering(canvas) {
            this.canvasDataUrl = canvas.toDataURL('image/png');
            this.rendering = false;
        },

        roundRect(ctx, x, y, width, height, radius) {
            ctx.beginPath();
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height - radius);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
            ctx.lineTo(x + radius, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.closePath();
        },

        wrapText(ctx, text, x, y, maxWidth, lineHeight, maxLines = 2) {
            if (!text) return;
            const words = text.split(' ');
            let line = '';
            let lineCount = 0;

            for (let n = 0; n < words.length; n++) {
                const testLine = line + words[n] + ' ';
                const metrics = ctx.measureText(testLine);
                const testWidth = metrics.width;
                if (testWidth > maxWidth && n > 0) {
                    ctx.fillText(line.trim(), x, y);
                    line = words[n] + ' ';
                    y += lineHeight;
                    lineCount++;
                    if (lineCount >= maxLines - 1 && n < words.length - 1) {
                        // Truncate with ellipsis
                        line = line.trim() + '...';
                        break;
                    }
                } else {
                    line = testLine;
                }
            }
            ctx.fillText(line.trim(), x, y);
        },

        downloadStoryImage() {
            if (!this.canvasDataUrl) return;
            const a = document.createElement('a');
            a.href = this.canvasDataUrl;
            a.download = `disekitar-story-${config.slug}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },

        async shareStory() {
            if (!this.canvasDataUrl) return;
            try {
                if (navigator.share) {
                    const blob = await (await fetch(this.canvasDataUrl)).blob();
                    const file = new File([blob], `disekitar-${config.slug}.png`, { type: 'image/png' });
                    await navigator.share({
                        title: config.title,
                        text: `Temukan ${config.title} di ${config.city} lewat panduan DISEKITAR!`,
                        url: config.url,
                        files: [file]
                    });
                } else {
                    this.downloadStoryImage();
                }
            } catch (err) {
                this.downloadStoryImage();
            }
        }
    }));
});
</script>
