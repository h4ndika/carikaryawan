/**
 * =========================================================================
 * CSR Engine Client (Lightweight Vanilla JS Engine)
 * =========================================================================
 * Engine Client-Side Rendering mandiri untuk web Vercel/GitHub Pages/Blogger
 * Menarik data dari Headless API Laravel disekitar.com dan merender UI lowongan.
 *
 * Cara Pasang di HTML manapun:
 *   <div id="csr-app"></div>
 *   <script 
 *     src="https://disekitar.com/csr/csr-engine.js" 
 *     data-api="https://disekitar.com/api/csr"
 *     data-theme="indigo"
 *   ></script>
 */

(function () {
    'use strict';

    // 1. Ambil script tag konfigurasi
    const currentScript = document.currentScript || (function() {
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

    const API_BASE = (currentScript && currentScript.getAttribute('data-api')) 
        ? currentScript.getAttribute('data-api').replace(/\/$/, '')
        : 'https://disekitar.com/api/csr';

    const CONTAINER_ID = (currentScript && currentScript.getAttribute('data-container')) || 'csr-app';
    const appEl = document.getElementById(CONTAINER_ID);

    if (!appEl) {
        console.warn(`[CSR Engine] Container #${CONTAINER_ID} tidak ditemukan di DOM.`);
        return;
    }

    // Dynamic config cache
    let remoteConfig = {
        ads: {
            adsterra_direct_link: '',
            adsterra_socialbar_code: '',
            apply_redirect_type: 'direct_link'
        }
    };

    // 2. Fetch Config & Ads
    fetch(`${API_BASE}/config`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.ads) {
                remoteConfig.ads = res.ads;
                injectAds(res.ads);
            }
        })
        .catch(() => {});

    // Injeksi Iklan (Social Bar & Directlink)
    function injectAds(ads) {
        if (ads.adsterra_socialbar_code) {
            try {
                const holder = document.createElement('div');
                holder.innerHTML = ads.adsterra_socialbar_code;
                const scripts = holder.querySelectorAll('script');
                scripts.forEach(s => {
                    const newScript = document.createElement('script');
                    if (s.src) newScript.src = s.src;
                    if (s.innerHTML) newScript.innerHTML = s.innerHTML;
                    document.body.appendChild(newScript);
                });
            } catch (e) {}
        }
    }

    // 3. Routing Resolver
    // Mendukung Hash routing (#/loker/slug) atau Query param (?slug=... atau ?id=...)
    function getRouteInfo() {
        const hash = window.location.hash.replace(/^#\/?/, '');
        const params = new URLSearchParams(window.location.search);

        if (hash.startsWith('loker/')) {
            return { view: 'detail', id: hash.replace('loker/', '') };
        }
        if (params.get('loker') || params.get('slug') || params.get('id')) {
            return { view: 'detail', id: params.get('loker') || params.get('slug') || params.get('id') };
        }

        const page = parseInt(params.get('page') || '1', 10);
        const q = params.get('q') || '';
        const city = params.get('city') || '';
        return { view: 'list', page, q, city };
    }

    // 4. Injeksi Style CSS Modern & Responsif (Tanpa Tailwind/Bootstrap eksternal)
    function injectStyles() {
        if (document.getElementById('csr-engine-styles')) return;
        const style = document.createElement('style');
        style.id = 'csr-engine-styles';
        style.textContent = `
            .csr-container { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1e293b; max-width: 1000px; margin: 0 auto; padding: 20px; line-height: 1.5; box-sizing: border-box; }
            .csr-header { text-align: center; margin-bottom: 28px; }
            .csr-header h1 { font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 8px; letter-spacing: -0.02em; }
            .csr-header p { font-size: 14px; color: #64748b; margin: 0; }
            .csr-search-bar { display: flex; gap: 8px; margin-bottom: 24px; max-width: 600px; margin-left: auto; margin-right: auto; }
            .csr-input { flex: 1; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; }
            .csr-input:focus { border-color: #2563eb; }
            .csr-btn { background: #2563eb; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background .2s; }
            .csr-btn:hover { background: #1d4ed8; }
            .csr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
            .csr-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; transition: transform .2s, box-shadow .2s; text-decoration: none; color: inherit; }
            .csr-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-color: #cbd5e1; }
            .csr-tag { display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; background: #eff6ff; color: #2563eb; margin-bottom: 10px; width: fit-content; }
            .csr-job-title { font-size: 16px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0; }
            .csr-job-meta { font-size: 13px; color: #475569; margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
            .csr-job-salary { font-size: 14px; font-weight: 700; color: #16a34a; margin: 10px 0 0 0; }
            .csr-loading { text-align: center; padding: 50px 20px; color: #64748b; font-size: 15px; }
            .csr-detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
            .csr-back-link { display: inline-flex; align-items: center; gap: 4px; color: #2563eb; text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 16px; cursor: pointer; }
            .csr-cta-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin: 24px 0; text-align: center; }
            .csr-cta-btn { display: inline-block; background: #16a34a; color: #ffffff !important; padding: 14px 28px; border-radius: 8px; font-size: 16px; font-weight: 700; text-decoration: none; box-shadow: 0 4px 10px rgba(22, 163, 74, 0.3); transition: background .2s; cursor: pointer; }
            .csr-cta-btn:hover { background: #15803d; }
            .csr-rich-text { font-size: 14px; line-height: 1.7; color: #334155; }
            .csr-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 28px; }
        `;
        document.head.appendChild(style);
    }

    // 5. Render Halaman List
    function renderList(page = 1, q = '', city = '') {
        appEl.innerHTML = `<div class="csr-loading">⏳ Memuat lowongan kerja terbaru...</div>`;

        const queryUrl = `${API_BASE}/jobs?page=${page}&q=${encodeURIComponent(q)}&city=${encodeURIComponent(city)}`;
        
        fetch(queryUrl)
            .then(res => res.json())
            .then(res => {
                if (!res.success || !res.data || res.data.length === 0) {
                    appEl.innerHTML = `
                        <div class="csr-header">
                            <h1>Lowongan Kerja Tersedia</h1>
                            <p>Temukan karir impian Anda se-Indonesia</p>
                        </div>
                        <div class="csr-loading">Belum ada lowongan yang sesuai pencarian.</div>
                    `;
                    return;
                }

                let html = `
                    <div class="csr-header">
                        <h1>Lowongan Kerja Terbaru</h1>
                        <p>Daftar lowongan kerja terverifikasi dan siap dilamar</p>
                    </div>
                    <form class="csr-search-bar" id="csr-filter-form">
                        <input type="text" class="csr-input" name="q" placeholder="Cari posisi (Staff, Admin, Barista...)" value="${escapeHtml(q)}">
                        <button type="submit" class="csr-btn">Cari</button>
                    </form>
                    <div class="csr-grid">
                `;

                res.data.forEach(job => {
                    html += `
                        <a href="#/loker/${job.slug}" class="csr-card" data-slug="${job.slug}">
                            <div>
                                <span class="csr-tag">${escapeHtml(job.type)}</span>
                                <h2 class="csr-job-title">${escapeHtml(job.title)}</h2>
                                <div class="csr-job-meta">🏢 ${escapeHtml(job.company)}</div>
                                <div class="csr-job-meta">📍 ${escapeHtml(job.location)}</div>
                            </div>
                            <div>
                                <div class="csr-job-salary">${escapeHtml(job.salary)}</div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:6px;">🕒 ${escapeHtml(job.published_at)}</div>
                            </div>
                        </a>
                    `;
                });

                html += `</div>`;

                // Pagination
                if (res.pagination && res.pagination.last_page > 1) {
                    html += `<div class="csr-pagination">`;
                    if (res.pagination.current_page > 1) {
                        html += `<button class="csr-btn" style="padding:8px 14px;" onclick="location.search='?page=${res.pagination.current_page - 1}&q=${encodeURIComponent(q)}'">« Sebelumnya</button>`;
                    }
                    html += `<span style="align-self:center;font-size:13px;color:#64748b;">Hal ${res.pagination.current_page} dari ${res.pagination.last_page}</span>`;
                    if (res.pagination.has_more) {
                        html += `<button class="csr-btn" style="padding:8px 14px;" onclick="location.search='?page=${res.pagination.current_page + 1}&q=${encodeURIComponent(q)}'">Selanjutnya »</button>`;
                    }
                    html += `</div>`;
                }

                appEl.innerHTML = html;

                // Form submit handler
                const filterForm = document.getElementById('csr-filter-form');
                if (filterForm) {
                    filterForm.onsubmit = function(e) {
                        e.preventDefault();
                        const searchVal = this.q.value.trim();
                        window.location.search = `?q=${encodeURIComponent(searchVal)}`;
                    };
                }
            })
            .catch(err => {
                appEl.innerHTML = `<div class="csr-loading" style="color:#ef4444;">Gagal memuat lowongan: ${err.message}</div>`;
            });
    }

    // 6. Render Halaman Detail
    function renderDetail(identifier) {
        appEl.innerHTML = `<div class="csr-loading">⏳ Memuat detail lowongan kerja...</div>`;

        fetch(`${API_BASE}/jobs/${encodeURIComponent(identifier)}`)
            .then(res => res.json())
            .then(res => {
                if (!res.success || !res.data) {
                    appEl.innerHTML = `
                        <div class="csr-detail-card" style="text-align:center;">
                            <h2>Lowongan Tidak Ditemukan</h2>
                            <p>Lowongan ini mungkin sudah ditutup atau telah kedaluwarsa.</p>
                            <a href="#" class="csr-back-link">« Kembali ke Daftar Lowongan</a>
                        </div>
                    `;
                    return;
                }

                const job = res.data;

                // Update Title & Meta Head dinamis untuk crawler
                if (job.meta && job.meta.seo_title) {
                    document.title = job.meta.seo_title;
                }

                // Tentukan Link Lamar (Monetisasi Switch)
                let applyUrl = remoteConfig.ads.adsterra_direct_link || job.original_apply_url || '#';
                let isDirectLink = false;

                if (remoteConfig.ads.apply_redirect_type === 'direct_link' && remoteConfig.ads.adsterra_direct_link) {
                    applyUrl = remoteConfig.ads.adsterra_direct_link;
                    isDirectLink = true;
                } else if (job.original_apply_url) {
                    applyUrl = job.original_apply_url;
                }

                let html = `
                    <a href="#" class="csr-back-link">« Kembali ke Semua Lowongan</a>
                    <div class="csr-detail-card">
                        <span class="csr-tag">${escapeHtml(job.type)}</span>
                        <h1 style="font-size:24px;font-weight:800;color:#0f172a;margin:8px 0;">${escapeHtml(job.title)}</h1>
                        <div class="csr-job-meta" style="font-size:15px;margin-bottom:8px;">🏢 <strong>${escapeHtml(job.company)}</strong></div>
                        <div class="csr-job-meta" style="font-size:14px;color:#64748b;">📍 ${escapeHtml(job.location)}</div>
                        <div class="csr-job-salary" style="font-size:18px;margin-top:12px;">💰 ${escapeHtml(job.salary)}</div>

                        <div class="csr-cta-box">
                            <h3 style="margin-top:0;font-size:16px;">Tertarik dengan posisi ini?</h3>
                            <p style="font-size:13px;color:#64748b;margin-bottom:14px;">Klik tombol di bawah untuk verifikasi dan kirim berkas lamaran Anda.</p>
                            <a href="${escapeHtml(applyUrl)}" target="_blank" rel="nofollow noopener" class="csr-cta-btn" id="csr-apply-action">
                                🚀 Lamar Pekerjaan Sekarang
                            </a>
                        </div>

                        ${job.requirements ? `
                            <h3 style="font-size:16px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-top:24px;">Kualifikasi & Persyaratan:</h3>
                            <div class="csr-rich-text">${formatText(job.requirements)}</div>
                        ` : ''}

                        ${job.responsibilities ? `
                            <h3 style="font-size:16px;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-top:24px;">Deskripsi Pekerjaan:</h3>
                            <div class="csr-rich-text">${formatText(job.responsibilities)}</div>
                        ` : ''}
                    </div>
                `;

                appEl.innerHTML = html;

                // Event listener tombol apply untuk trigger direct link jika diset
                const applyBtn = document.getElementById('csr-apply-action');
                if (applyBtn && isDirectLink) {
                    applyBtn.onclick = function() {
                        // Buka direct link di tab baru
                        window.open(applyUrl, '_blank');
                        return false;
                    };
                }
            })
            .catch(err => {
                appEl.innerHTML = `<div class="csr-loading" style="color:#ef4444;">Gagal memuat detail lowongan: ${err.message}</div>`;
            });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    function formatText(text) {
        if (!text) return '';
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    // 7. Inisialisasi & Popstate Listener
    function init() {
        injectStyles();
        const route = getRouteInfo();
        if (route.view === 'detail') {
            renderDetail(route.id);
        } else {
            renderList(route.page, route.q, route.city);
        }
    }

    window.addEventListener('hashchange', init);
    window.addEventListener('popstate', init);

    init();
})();
