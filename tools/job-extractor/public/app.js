/**
 * Job Extractor Dashboard Frontend Client
 */

// ─── State ──────────────────────────────────────────────────────
const state = {
    isScraping: false,
    currentEngine: 'spidersource/google-jobs-scraper',
    activeFile: null,
    currentJobs: [],
    filteredJobs: [],
    files: [],
    config: {},
};

// ─── DOM References ─────────────────────────────────────────────
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

const elements = {
    // Tabs
    tabButtons: $$('.tab-btn'),
    tabContents: $$('.tab-content'),
    tabResultsCount: $('#tab-results-count'),

    // Stats
    statTotalJobs: $('#stat-total-jobs'),
    statTotalFiles: $('#stat-total-files'),
    statTokenStatus: $('#stat-token-status'),
    statScraperStatus: $('#stat-scraper-status'),

    // Scraper Tab
    formScrape: $('#form-scrape'),
    engineCards: $$('.engine-card'),
    inputQuery: $('#input-query'),
    labelQuery: $('#label-query'),
    hintQuery: $('#hint-query'),
    inputLimit: $('#input-limit'),
    valLimit: $('#val-limit'),
    btnStartScrape: $('#btn-start-scrape'),
    btnStartText: $('#btn-start-text'),
    terminalLogs: $('#terminal-logs'),
    checkAutoscroll: $('#check-autoscroll'),
    btnClearLogs: $('#btn-clear-logs'),

    // Results Tab
    selectResultFile: $('#select-result-file'),
    inputSearchJobs: $('#input-search-jobs'),
    btnDownloadJson: $('#btn-download-json'),
    btnCopyJson: $('#btn-copy-json'),
    resultsCountText: $('#results-count-text'),
    resultsFileInfo: $('#results-file-info'),
    jobsGrid: $('#jobs-grid'),

    // Files Tab
    filesTableBody: $('#files-table-body'),
    btnRefreshFiles: $('#btn-refresh-files'),

    // Settings Tab
    formSettings: $('#form-settings'),
    inputApifyToken: $('#input-apify-token'),

    // Modal
    modalJobDetail: $('#modal-job-detail'),
    modalTitle: $('#modal-title'),
    modalContent: $('#modal-content'),
    modalApplyLink: $('#modal-apply-link'),
    btnCloseModal: $('#btn-close-modal'),

    // Toast
    toastContainer: $('#toast-container'),
};

// ─── Toast Notifications ────────────────────────────────────────
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    toast.innerHTML = `<span>${icon}</span><span>${escapeHtml(message)}</span>`;
    elements.toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ─── Tab Switching ──────────────────────────────────────────────
function switchTab(tabId) {
    elements.tabButtons.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabId);
    });
    elements.tabContents.forEach(content => {
        content.classList.toggle('active', content.id === `tab-${tabId}`);
    });

    if (tabId === 'files') {
        loadFilesList();
    } else if (tabId === 'results' && !state.activeFile && state.files.length > 0) {
        // Otomatis pilih file terbaru jika belum ada yang aktif
        elements.selectResultFile.value = state.files[0].fileName;
        loadFileContent(state.files[0].fileName);
    }
}

// ─── Engine Selection Logic ─────────────────────────────────────
function updateEngineFields(engine) {
    state.currentEngine = engine;
    elements.engineCards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        if (radio.value === engine) {
            radio.checked = true;
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });

    if (engine === 'spidersource/google-jobs-scraper') {
        elements.labelQuery.textContent = 'Keyword Posisi & Lokasi';
        elements.inputQuery.placeholder = 'Contoh: lowongan barista bandung, staff toko surabaya';
        elements.inputQuery.value = 'lowongan barista bandung';
        elements.hintQuery.textContent = 'Bisa masukkan beberapa keyword posisi & kota dipisahkan dengan koma.';
    } else {
        elements.labelQuery.textContent = 'Username Akun Instagram Loker';
        elements.inputQuery.placeholder = 'Contoh: lokerbandung, lokerbdg, loker_jakarta';
        elements.inputQuery.value = 'lokerbandung';
        elements.hintQuery.textContent = 'Masukkan username akun IG komunitas loker (tanpa @), pisahkan dengan koma.';
    }
}

// ─── SSE Real-time Logs ─────────────────────────────────────────
let sseSource = null;

function connectSSE() {
    if (sseSource) sseSource.close();

    sseSource = new EventSource('/api/events');

    sseSource.onmessage = (event) => {
        try {
            const data = JSON.parse(event.data);
            if (data.type === 'connected') return;

            appendLog(data.level || 'info', data.message || '', data.timestamp);

            // Periksa jika selesai atau error
            if (data.level === 'success' || data.level === 'error') {
                updateScraperState(false);
                loadStats();
                loadFilesList();
            }
        } catch {}
    };

    sseSource.onerror = () => {
        setTimeout(connectSSE, 5000);
    };
}

function appendLog(level, message, timestamp = null) {
    const time = timestamp 
        ? new Date(timestamp).toLocaleTimeString() 
        : new Date().toLocaleTimeString();

    const line = document.createElement('div');
    line.className = `log-line log-${level}`;
    line.innerHTML = `<span class="log-time">[${time}]</span> ${escapeHtml(message)}`;
    elements.terminalLogs.appendChild(line);

    if (elements.checkAutoscroll.checked) {
        elements.terminalLogs.scrollTop = elements.terminalLogs.scrollHeight;
    }
}

function updateScraperState(running) {
    state.isScraping = running;
    if (running) {
        elements.statScraperStatus.innerHTML = '<span class="status-dot dot-running"></span> Scraping...';
        elements.btnStartScrape.disabled = true;
        elements.btnStartText.textContent = 'Sedang Berjalan di Apify...';
    } else {
        elements.statScraperStatus.innerHTML = '<span class="status-dot dot-idle"></span> Standby';
        elements.btnStartScrape.disabled = false;
        elements.btnStartText.textContent = 'Mulai Ekstraksi Lowongan';
    }
}

// ─── API Requests ───────────────────────────────────────────────
async function loadStats() {
    try {
        const res = await fetch('/api/stats');
        const data = await res.json();

        elements.statTotalJobs.textContent = (data.totalJobs || 0).toLocaleString();
        elements.statTotalFiles.textContent = (data.totalFiles || 0).toLocaleString();
        elements.tabResultsCount.textContent = (data.totalJobs || 0).toLocaleString();

        if (data.hasToken) {
            elements.statTokenStatus.innerHTML = `<span class="text-emerald">✓ Terhubung</span> (${data.maskedToken})`;
        } else {
            elements.statTokenStatus.innerHTML = `<span class="text-rose">✕ Belum Disetel</span>`;
        }

        updateScraperState(data.isScraping);
    } catch {}
}

async function loadFilesList() {
    try {
        const res = await fetch('/api/results');
        const data = await res.json();
        state.files = data.files || [];

        // Update Select dropdown in results
        elements.selectResultFile.innerHTML = '';
        if (state.files.length === 0) {
            elements.selectResultFile.innerHTML = '<option value="">Belum ada file hasil</option>';
        } else {
            state.files.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.fileName;
                const timeStr = new Date(f.updatedAt).toLocaleDateString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit'
                });
                opt.textContent = `${f.fileName} (${f.itemCount} lowongan · ${timeStr})`;
                elements.selectResultFile.appendChild(opt);
            });

            // Restore active selection or set default
            if (state.activeFile) {
                elements.selectResultFile.value = state.activeFile;
            }
        }

        // Render Files Table in Tab 3
        renderFilesTable(state.files);
    } catch (err) {
        showToast('Gagal memuat berkas hasil: ' + err.message, 'error');
    }
}

function renderFilesTable(files) {
    if (files.length === 0) {
        elements.filesTableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">Belum ada file hasil ekstraksi di folder output/</td>
            </tr>
        `;
        return;
    }

    elements.filesTableBody.innerHTML = files.map(f => {
        const sizeKb = (f.sizeBytes / 1024).toFixed(1);
        const timeStr = new Date(f.updatedAt).toLocaleString('id-ID');
        return `
            <tr>
                <td><strong>📄 ${escapeHtml(f.fileName)}</strong></td>
                <td><span class="badge badge-primary">${f.itemCount} Lowongan</span></td>
                <td>${sizeKb} KB</td>
                <td class="text-muted">${timeStr}</td>
                <td>
                    <div style="display:flex; gap:6px;">
                        <button type="button" class="btn-sm btn-secondary" onclick="viewFile('${escapeHtml(f.fileName)}')">👁️ Lihat</button>
                        <a href="/api/results/${encodeURIComponent(f.fileName)}" download="${escapeHtml(f.fileName)}" class="btn-sm btn-ghost" style="text-decoration:none;">📥 Unduh</a>
                        <button type="button" class="btn-sm btn-ghost" style="color:var(--accent-rose);" onclick="deleteFile('${escapeHtml(f.fileName)}')">🗑️</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.viewFile = (fileName) => {
    switchTab('results');
    elements.selectResultFile.value = fileName;
    loadFileContent(fileName);
};

window.deleteFile = async (fileName) => {
    if (!confirm(`Hapus berkas ${fileName}?`)) return;
    try {
        const res = await fetch(`/api/results/${encodeURIComponent(fileName)}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            showToast(`Berkas ${fileName} berhasil dihapus`, 'success');
            if (state.activeFile === fileName) {
                state.activeFile = null;
                state.currentJobs = [];
                renderJobsGrid([]);
            }
            loadFilesList();
            loadStats();
        }
    } catch (err) {
        showToast('Gagal menghapus berkas: ' + err.message, 'error');
    }
};

async function loadFileContent(fileName) {
    if (!fileName) return;
    state.activeFile = fileName;
    elements.jobsGrid.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">⏳</div>
            <h3>Memuat Data Lowongan...</h3>
            <p>Membaca berkas ${escapeHtml(fileName)}</p>
        </div>
    `;

    try {
        const res = await fetch(`/api/results/${encodeURIComponent(fileName)}`);
        const json = await res.json();

        state.currentJobs = json.data || [];
        state.filteredJobs = [...state.currentJobs];

        elements.btnDownloadJson.disabled = false;
        elements.btnCopyJson.disabled = false;
        elements.resultsFileInfo.textContent = fileName;

        applyJobsFilter();
    } catch (err) {
        showToast('Gagal memuat isi file: ' + err.message, 'error');
    }
}

// ─── Filter & Render Jobs ───────────────────────────────────────
function applyJobsFilter() {
    const q = elements.inputSearchJobs.value.trim().toLowerCase();
    if (!q) {
        state.filteredJobs = [...state.currentJobs];
    } else {
        state.filteredJobs = state.currentJobs.filter(j => {
            const title = (j.title || j.posisi || '').toLowerCase();
            const company = (j.company || (j.details && j.details.perusahaan) || '').toLowerCase();
            const city = (j.city || j.location || '').toLowerCase();
            const desc = (j.description || '').toLowerCase();
            return title.includes(q) || company.includes(q) || city.includes(q) || desc.includes(q);
        });
    }

    elements.resultsCountText.textContent = `Menampilkan ${state.filteredJobs.length} dari ${state.currentJobs.length} lowongan`;
    renderJobsGrid(state.filteredJobs);
}

function renderJobsGrid(jobs) {
    if (jobs.length === 0) {
        elements.jobsGrid.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <h3>Tidak Ada Lowongan yang Cocok</h3>
                <p>Coba kata kunci pencarian lain atau pilih berkas hasil scraping lainnya.</p>
            </div>
        `;
        return;
    }

    elements.jobsGrid.innerHTML = jobs.map((job, idx) => {
        const title = job.title || 'Lowongan Kerja';
        const company = (job.details && job.details.perusahaan) || job.company || 'Perusahaan';
        const city = job.city || job.location || 'Indonesia';
        const salary = (job.details && job.details.gaji) || job.salary || null;
        const applyUrl = job.apply_url || job.url || '#';
        const snippet = job.description || 'Tidak ada deskripsi rinci.';
        const engineLabel = job.raw_engine === 'apify/instagram-post-scraper' || (job.details && job.details.posisi_ekstraksi === 'instagram')
            ? '📸 Instagram'
            : '🔍 Google Jobs';

        return `
            <div class="job-card">
                <div>
                    <div class="job-header">
                        <h3 class="job-title">${escapeHtml(title)}</h3>
                        <span class="badge badge-subtle">${engineLabel}</span>
                    </div>
                    <div class="job-company">🏢 ${escapeHtml(company)}</div>
                    
                    <div class="job-meta-row">
                        <span class="job-pill">📍 ${escapeHtml(city)}</span>
                        ${salary ? `<span class="job-pill" style="color:#34d399; border-color:rgba(16, 185, 129, 0.3);">💰 ${escapeHtml(salary)}</span>` : ''}
                        <span class="job-pill">🏷️ ${escapeHtml(job.category || 'Lowongan Kerja')}</span>
                    </div>

                    <p class="job-snippet">${escapeHtml(snippet)}</p>
                </div>

                <div class="job-footer">
                    <button type="button" class="btn-sm btn-secondary" onclick="openJobDetail(${idx})">
                        📄 Lihat Detail
                    </button>
                    ${applyUrl !== '#' ? `
                        <a href="${escapeHtml(applyUrl)}" target="_blank" rel="noopener noreferrer" class="btn-sm btn-primary" style="text-decoration:none;">
                            🚀 Lamar
                        </a>
                    ` : `
                        <span class="badge badge-subtle">Cek Caption</span>
                    `}
                </div>
            </div>
        `;
    }).join('');
}

window.openJobDetail = (index) => {
    const job = state.filteredJobs[index];
    if (!job) return;

    elements.modalTitle.textContent = job.title || 'Detail Lowongan';
    const company = (job.details && job.details.perusahaan) || job.company || '-';
    const city = job.city || job.location || '-';
    const salary = (job.details && job.details.gaji) || job.salary || 'Tidak disebutkan';
    const applyUrl = job.apply_url || job.url || '#';

    let detailsHtml = '';
    if (job.details && typeof job.details === 'object') {
        detailsHtml = `
            <div style="background:var(--bg-input); padding:12px; border-radius:8px; margin-top:14px;">
                <h4 style="font-size:12px; color:var(--text-muted); text-transform:uppercase; margin-bottom:8px;">Informasi Tambahan:</h4>
                <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:4px; font-size:12px;">
                    ${Object.entries(job.details).map(([k, v]) => `
                        <li><strong>${escapeHtml(k)}:</strong> ${escapeHtml(String(v))}</li>
                    `).join('')}
                </ul>
            </div>
        `;
    }

    elements.modalContent.innerHTML = `
        <div style="display:flex; flex-direction:column; gap:10px;">
            <div>
                <strong style="color:var(--text-muted); font-size:12px;">PERUSAHAAN / INSTANSI:</strong>
                <div style="font-size:15px; font-weight:700; color:var(--accent-blue);">${escapeHtml(company)}</div>
            </div>
            <div>
                <strong style="color:var(--text-muted); font-size:12px;">LOKASI / KOTA:</strong>
                <div>📍 ${escapeHtml(city)}</div>
            </div>
            <div>
                <strong style="color:var(--text-muted); font-size:12px;">ESTIMASI GAJI:</strong>
                <div style="color:#34d399; font-weight:600;">💰 ${escapeHtml(salary)}</div>
            </div>
            <div style="margin-top:8px;">
                <strong style="color:var(--text-muted); font-size:12px;">DESKRIPSI LENGKAP & KUALIFIKASI:</strong>
                <div style="white-space:pre-wrap; background:var(--bg-input); padding:14px; border-radius:8px; margin-top:6px; max-height:260px; overflow-y:auto; font-size:12px; line-height:1.6;">${escapeHtml(job.description || 'Tidak ada keterangan tambahan.')}</div>
            </div>
            ${detailsHtml}
        </div>
    `;

    if (applyUrl && applyUrl !== '#') {
        elements.modalApplyLink.href = applyUrl;
        elements.modalApplyLink.style.display = 'inline-flex';
    } else {
        elements.modalApplyLink.style.display = 'none';
    }

    elements.modalJobDetail.classList.add('show');
};

// ─── Event Handlers ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Connect SSE
    connectSSE();

    // Initial API calls
    loadStats();
    loadFilesList();

    // Tab buttons
    elements.tabButtons.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });

    // Engine Radio Cards
    elements.engineCards.forEach(card => {
        card.addEventListener('click', () => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio) updateEngineFields(radio.value);
        });
    });

    // Limit Slider
    elements.inputLimit.addEventListener('input', (e) => {
        elements.valLimit.textContent = e.target.value;
    });

    // Scrape Form Submit
    elements.formScrape.addEventListener('submit', async (e) => {
        e.preventDefault();

        const engine = document.querySelector('input[name="engine"]:checked').value;
        const query = elements.inputQuery.value.trim();
        const maxItems = parseInt(elements.inputLimit.value, 10) || 20;

        if (!query) {
            showToast('Harap masukkan keyword pencarian atau username!', 'error');
            return;
        }

        updateScraperState(true);
        appendLog('info', `Mengirim permintaan ekstraksi [${engine}] "${query}" limit ${maxItems}...`);

        try {
            const res = await fetch('/api/scrape', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ engine, query, maxItems })
            });
            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || 'Terjadi kesalahan pada server');
            }

            showToast('Ekstraksi dimulai di background! Pantau live log terminal.', 'info');
        } catch (err) {
            updateScraperState(false);
            appendLog('error', `Gagal memulai: ${err.message}`);
            showToast(err.message, 'error');
        }
    });

    // Clear Terminal Logs
    elements.btnClearLogs.addEventListener('click', () => {
        elements.terminalLogs.innerHTML = `
            <div class="log-line log-system">
                <span class="log-time">[System]</span> Log dibersihkan.
            </div>
        `;
    });

    // File selection change
    elements.selectResultFile.addEventListener('change', (e) => {
        loadFileContent(e.target.value);
    });

    // Search filter input
    elements.inputSearchJobs.addEventListener('input', applyJobsFilter);

    // Download current JSON
    elements.btnDownloadJson.addEventListener('click', () => {
        if (!state.activeFile) return;
        window.location.href = `/api/results/${encodeURIComponent(state.activeFile)}`;
    });

    // Copy current JSON
    elements.btnCopyJson.addEventListener('click', async () => {
        if (state.currentJobs.length === 0) return;
        try {
            await navigator.clipboard.writeText(JSON.stringify(state.currentJobs, null, 2));
            showToast('JSON berhasil disalin ke clipboard!', 'success');
        } catch {
            showToast('Gagal menyalin ke clipboard', 'error');
        }
    });

    // Refresh Files
    elements.btnRefreshFiles.addEventListener('click', () => {
        loadFilesList();
        showToast('Daftar berkas disegarkan', 'info');
    });

    // Settings Form
    elements.formSettings.addEventListener('submit', async (e) => {
        e.preventDefault();
        const token = elements.inputApifyToken.value.trim();
        if (!token) return;

        try {
            const res = await fetch('/api/config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Token Apify berhasil disimpan ke .env!', 'success');
                elements.inputApifyToken.value = '';
                loadStats();
            } else {
                showToast(data.error || 'Gagal menyimpan token', 'error');
            }
        } catch (err) {
            showToast('Gagal: ' + err.message, 'error');
        }
    });

    // Modal Close
    elements.btnCloseModal.addEventListener('click', () => {
        elements.modalJobDetail.classList.remove('show');
    });
    elements.modalJobDetail.addEventListener('click', (e) => {
        if (e.target === elements.modalJobDetail) {
            elements.modalJobDetail.classList.remove('show');
        }
    });
});
