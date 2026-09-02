/* ─── Google Maps Extractor v2 — Dashboard App ─────────────────── */

const API = ''; // Empty base = same origin

// ─── STATE ───────────────────────────────────────────────────────
const state = {
    activeTab: 'scraper',
    accounts: [],
    tasks: [],
    outputs: [],
    masterPlaces: [],
    masterPlacesTotal: 0,
    masterPlacesOffset: 0,
    masterPlacesLimit: 50,
    currentTaskPlaces: [],
    categories: [],
    logLines: [],
    sseSource: null,
    scraping: false,
    scrapeStart: null,
    scrapeTimer: null,
};

// ─── INIT ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initScrapeForm();
    initAccountActions();
    initHistoryActions();
    initOutputsActions();
    initMasterPlacesActions();
    initWorkerActions();
    initModals();
    initLogConsole();
    
    // Initial data fetch
    loadStats();
    loadAccounts();
    loadHistory();
    loadOutputs();
    loadCategories();
    loadTaskFilters();
    loadMasterPlaces();
    loadWorkerData();
    connectSSE();
});

// ─── TABS ────────────────────────────────────────────────────────
function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const targetContent = document.getElementById(`tab-${tab}`);
            if (targetContent) targetContent.classList.add('active');
            state.activeTab = tab;

            if (tab === 'history') loadHistory();
            if (tab === 'results') {
                loadOutputs();
                loadMasterPlaces();
            }
            if (tab === 'workers') loadWorkerData();
            if (tab === 'accounts') loadAccounts();
        });
    });

    document.getElementById('view-all-results-btn')?.addEventListener('click', () => {
        document.querySelector('.tab-btn[data-tab="results"]')?.click();
    });
}

// ─── STATS ───────────────────────────────────────────────────────
async function loadStats() {
    try {
        const data = await api('/api/stats');
        setId('stat-accounts', data.totalAccounts ?? 0);
        setId('stat-active', data.activeAccounts ?? data.activeTokens ?? 0);
        setId('stat-places', formatNum(data.totalPlaces ?? 0));
        setId('stat-tasks', data.totalScrapes ?? data.totalTasks ?? 0);
    } catch {
        // Fallback
    }
}

// ─── LOCATION AUTOCOMPLETE ───────────────────────────────────────
function initLocationAutocomplete() {
    const input = document.getElementById('location-filter');
    const dropdown = document.getElementById('location-dropdown');
    if (!input || !dropdown) return;

    let debounceTimer;
    let selectedIndex = -1;
    let currentResults = [];

    const fetchAndShow = async (query = '') => {
        try {
            const list = await api(`/api/locations?q=${encodeURIComponent(query)}`);
            currentResults = Array.isArray(list) ? list : [];
            selectedIndex = -1;

            if (!currentResults.length) {
                dropdown.innerHTML = '<div style="padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--text-dim); text-align: center;">Lokasi tidak ditemukan.</div>';
                dropdown.classList.add('open');
                return;
            }

            dropdown.innerHTML = currentResults.map((item, i) => `
                <div class="autocomplete-item" data-index="${i}" data-value="${esc(item.value)}">
                    <span>${esc(item.label || item.name)}</span>
                    <span class="autocomplete-item-type">${esc(item.type || 'kota')}</span>
                </div>
            `).join('');

            dropdown.querySelectorAll('.autocomplete-item').forEach((el) => {
                el.addEventListener('click', () => {
                    const val = el.dataset.value;
                    input.value = val;
                    dropdown.classList.remove('open');
                });
            });

            dropdown.classList.add('open');
        } catch {
            dropdown.classList.remove('open');
        }
    };

    // Show options on focus
    input.addEventListener('focus', () => {
        fetchAndShow(input.value.trim());
    });

    // Debounced search on input
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchAndShow(input.value.trim());
        }, 200);
    });

    // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
    input.addEventListener('keydown', (e) => {
        if (!dropdown.classList.contains('open')) return;

        const items = dropdown.querySelectorAll('.autocomplete-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            highlightItem(items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            highlightItem(items, selectedIndex);
        } else if (e.key === 'Enter') {
            if (selectedIndex >= 0 && items[selectedIndex]) {
                e.preventDefault();
                input.value = items[selectedIndex].dataset.value;
                dropdown.classList.remove('open');
            }
        } else if (e.key === 'Escape') {
            dropdown.classList.remove('open');
        }
    });

    function highlightItem(items, idx) {
        items.forEach((it, i) => {
            if (i === idx) {
                it.classList.add('selected');
                it.scrollIntoView({ block: 'nearest' });
            } else {
                it.classList.remove('selected');
            }
        });
    }

    // Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.autocomplete-wrapper')) {
            dropdown.classList.remove('open');
        }
    });
}

// ─── SCRAPE FORM ─────────────────────────────────────────────────
function initScrapeForm() {
    const slider = document.getElementById('max-places');
    const label = document.getElementById('max-places-value');
    if (slider && label) {
        slider.addEventListener('input', () => { label.textContent = slider.value; });
    }

    const modeSelect = document.getElementById('account-mode');
    const accountSelect = document.getElementById('account-select');
    if (modeSelect && accountSelect) {
        modeSelect.addEventListener('change', () => {
            accountSelect.style.display = modeSelect.value === 'manual' ? 'block' : 'none';
        });
    }

    // Location Autocomplete with Indonesian Cities/Regencies/Provinces
    initLocationAutocomplete();

    document.getElementById('scrape-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (state.scraping) return;
        startScrape();
    });

    document.getElementById('clear-log-btn')?.addEventListener('click', () => {
        const term = document.getElementById('scrape-terminal');
        if (term) term.innerHTML = '';
    });
}

async function startScrape() {
    const btn = document.getElementById('start-scrape-btn');
    const query = document.getElementById('search-query').value.trim();
    if (!query) { toast('Enter a search query', 'warn'); return; }

    const formats = [...document.querySelectorAll('input[name="format"]:checked')].map(c => c.value);
    let format = 'none';
    if (formats.length === 4) {
        format = 'all';
    } else if (formats.length > 0) {
        format = formats.join(',');
    }

    const accountMode = document.getElementById('account-mode').value;
    const selectedAccId = document.getElementById('account-select').value;

    const body = {
        query,
        location: document.getElementById('location-filter').value.trim(),
        maxPlaces: +document.getElementById('max-places').value,
        format,
        rotateAccount: accountMode === 'auto',
        accountId: accountMode === 'manual' && selectedAccId ? Number(selectedAccId) : undefined,
        includeReviews: document.getElementById('include-reviews').checked,
        includeImages: document.getElementById('include-images').checked,
        language: document.getElementById('language').value,
        countryCode: document.getElementById('country').value,
    };

    state.scraping = true;
    btn.classList.add('btn-loading');
    btn.innerHTML = '<span class="btn-icon">⏳</span><span>Scraping...</span>';
    
    document.getElementById('scrape-progress').style.display = 'block';
    document.getElementById('scrape-metrics').style.display = 'flex';
    setId('scrape-status', 'Running');
    setId('places-found', '...');
    state.scrapeStart = Date.now();
    state.scrapeTimer = setInterval(updateDuration, 1000);

    appendLog('scrape-terminal', 'info', `Starting scrape: "${query}"`);

    try {
        const data = await api('/api/scrape', 'POST', body);
        setId('scrape-status', 'Completed');
        const count = data.count ?? data.placesCount ?? 0;
        appendLog('scrape-terminal', 'success', `Scrape finished — ${count} places extracted & saved!`);
        setId('places-found', count);
        document.getElementById('progress-fill').style.width = '100%';

        // Fetch the places from the newly created task to show in preview
        if (data.taskId) {
            try {
                const taskPlaces = await api(`/api/tasks/${data.taskId}/places`);
                if (taskPlaces?.length) showResultsPreview(taskPlaces);
            } catch {}
        }

        loadStats();
        loadHistory();
        loadOutputs();
        loadMasterPlaces();
        toast(`Scrape completed — ${count} places saved!`, 'success');
    } catch (err) {
        setId('scrape-status', 'Error');
        appendLog('scrape-terminal', 'error', `Scrape failed: ${err.message}`);
        toast(`Scrape failed: ${err.message}`, 'error');
    } finally {
        state.scraping = false;
        clearInterval(state.scrapeTimer);
        btn.classList.remove('btn-loading');
        btn.innerHTML = '<span class="btn-icon">🚀</span><span>Start Scrape</span>';
    }
}

function updateDuration() {
    if (!state.scrapeStart) return;
    const s = Math.round((Date.now() - state.scrapeStart) / 1000);
    setId('scrape-duration', s < 60 ? `${s}s` : `${Math.floor(s/60)}m ${s%60}s`);
}

function showResultsPreview(items) {
    const container = document.getElementById('results-preview');
    const tbody = document.getElementById('results-tbody');
    if (!container || !tbody) return;
    container.style.display = 'block';
    tbody.innerHTML = items.slice(0, 20).map((p, idx) => `
        <tr onclick="showPlaceDetailByIndex(${idx}, 'preview')">
            <td><strong>${esc(p.title || p.name || '-')}</strong></td>
            <td><span class="badge badge-primary">${esc(p.category || p.categoryName || '-')}</span></td>
            <td>${renderStars(p.rating || p.totalScore)}</td>
            <td>${esc(truncate(p.address || '-', 40))}</td>
            <td>${esc(p.phone || '-')}</td>
            <td>
                <button class="btn btn-sm btn-outline" onclick="event.stopPropagation(); showPlaceDetailByIndex(${idx}, 'preview')">👁 Detail</button>
            </td>
        </tr>
    `).join('');

    // Cache preview places
    window.__previewPlaces = items;
}

// ─── ACCOUNTS ────────────────────────────────────────────────────
function initAccountActions() {
    document.getElementById('add-account-btn')?.addEventListener('click', () => openModal('add-account-modal'));
    document.getElementById('auto-create-account-btn')?.addEventListener('click', () => openModal('auto-create-modal'));

    document.getElementById('add-account-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('account-email').value.trim();
        const token = document.getElementById('account-token').value.trim();
        if (!token) { toast('API Token is required', 'warn'); return; }
        try {
            await api('/api/accounts', 'POST', { email, token });
            toast('Account added to SQLite database', 'success');
            closeModal('add-account-modal');
            loadAccounts();
            loadStats();
        } catch (err) {
            toast(`Failed: ${err.message}`, 'error');
        }
    });

    document.getElementById('start-auto-create-btn')?.addEventListener('click', async () => {
        const term = document.getElementById('auto-create-terminal');
        const headless = document.getElementById('headless-mode').checked;
        const method = document.getElementById('create-method')?.value || 'github';
        const btn = document.getElementById('start-auto-create-btn');
        btn.classList.add('btn-loading');
        btn.textContent = 'Creating account...';
        term.innerHTML = '';
        appendLog('auto-create-terminal', 'info', `Starting automated Apify creation via ${method.toUpperCase()}...`);
        try {
            const data = await api('/api/accounts/generate', 'POST', { headless, method });
            appendLog('auto-create-terminal', 'success', `Account created successfully: ${data.account?.email || data.email || 'OK'}`);
            toast('Account auto-created and saved to SQLite!', 'success');
            loadAccounts();
            loadStats();
        } catch (err) {
            appendLog('auto-create-terminal', 'error', `Failed: ${err.message}`);
            toast(`Auto-create failed: ${err.message}`, 'error');
        } finally {
            btn.classList.remove('btn-loading');
            btn.textContent = 'Start Creation';
        }
    });

    document.getElementById('account-search')?.addEventListener('input', renderAccountsTable);
    document.getElementById('account-filter')?.addEventListener('change', renderAccountsTable);
}

async function loadAccounts() {
    try {
        const res = await api('/api/accounts');
        state.accounts = Array.isArray(res) ? res : (res.accounts || []);
        renderAccountsTable();
        populateAccountSelect();
    } catch {
        state.accounts = [];
        renderAccountsTable();
    }
}

function populateAccountSelect() {
    const sel = document.getElementById('account-select');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Select Account —</option>' +
        state.accounts.filter(a => a.status === 'active').map(a =>
            `<option value="${a.id}">#${a.id} ${esc(a.email || a.token?.slice(0,12) + '...')} (${a.usageCount ?? a.usage_count ?? 0}x used)</option>`
        ).join('');
}

function renderAccountsTable() {
    const tbody = document.getElementById('accounts-tbody');
    if (!tbody) return;
    const search = (document.getElementById('account-search')?.value || '').toLowerCase();
    const filter = document.getElementById('account-filter')?.value || 'all';

    let list = state.accounts;
    if (search) list = list.filter(a => (a.email || '').toLowerCase().includes(search) || (a.token || '').toLowerCase().includes(search));
    if (filter !== 'all') list = list.filter(a => a.status === filter);

    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="8">
            <div class="empty-state">
                <div class="empty-state-icon">🔑</div>
                <div class="empty-state-text">No accounts found in database. Add one manually or use 1-Click Auto-Create!</div>
            </div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(a => {
        const id = a.id;
        const email = a.email || '-';
        const token = a.token || '';
        const status = a.status || 'active';
        const usage = a.usageCount ?? a.usage_count ?? 0;
        const lastUsed = a.lastUsedAt ?? a.last_used_at;
        const createdAt = a.createdAt ?? a.created_at;

        return `
        <tr>
            <td><code>#${id}</code></td>
            <td><strong>${esc(email)}</strong></td>
            <td>
                <div class="token-cell">
                    <span class="token-text" id="tok-${id}" data-full="${esc(token)}" data-masked="true">
                        ${maskToken(token)}
                    </span>
                    <button class="token-btn" title="Toggle Mask" onclick="toggleToken('${id}')">👁</button>
                    <button class="token-btn" title="Copy Token" onclick="copyToken('${id}')">📋</button>
                </div>
            </td>
            <td><span class="badge badge-${statusColor(status)}">${esc(status.toUpperCase())}</span></td>
            <td><span class="badge badge-neutral">${usage}x</span></td>
            <td>${formatDate(lastUsed)}</td>
            <td>${formatDate(createdAt)}</td>
            <td>
                <div class="action-btns">
                    <button class="action-btn" title="Toggle Status (Active/Disabled)" onclick="toggleAccountStatus(${id})">🔄</button>
                    <button class="action-btn danger" title="Delete Account" onclick="deleteAccount(${id})">🗑️</button>
                </div>
            </td>
        </tr>
    `}).join('');
}

window.toggleToken = function(id) {
    const el = document.getElementById(`tok-${id}`);
    if (!el) return;
    const masked = el.dataset.masked === 'true';
    el.textContent = masked ? el.dataset.full : maskToken(el.dataset.full);
    el.dataset.masked = masked ? 'false' : 'true';
};

window.copyToken = function(id) {
    const el = document.getElementById(`tok-${id}`);
    if (!el) return;
    navigator.clipboard.writeText(el.dataset.full).then(() => toast('API Token copied to clipboard', 'info'));
};

window.toggleAccountStatus = async function(id) {
    const acc = state.accounts.find(a => a.id === id);
    const newStatus = acc?.status === 'active' ? 'disabled' : 'active';
    try {
        await api(`/api/accounts/${id}/status`, 'PATCH', { status: newStatus });
        toast(`Account #${id} status changed to ${newStatus}`, 'info');
        loadAccounts();
    } catch (err) { toast(`Failed: ${err.message}`, 'error'); }
};

window.deleteAccount = async function(id) {
    if (!confirm(`Delete Apify account #${id} from database?`)) return;
    try {
        await api(`/api/accounts/${id}`, 'DELETE');
        toast('Account deleted', 'info');
        loadAccounts();
        loadStats();
    } catch (err) { toast(`Failed: ${err.message}`, 'error'); }
};

// ─── HISTORY (TAB 2) ─────────────────────────────────────────────
function initHistoryActions() {
    document.getElementById('refresh-history-btn')?.addEventListener('click', () => {
        loadHistory();
        toast('History refreshed', 'info');
    });
    document.getElementById('history-search')?.addEventListener('input', renderHistoryTable);
    document.getElementById('history-date')?.addEventListener('change', renderHistoryTable);
}

async function loadHistory() {
    try {
        const res = await api('/api/tasks');
        state.tasks = Array.isArray(res) ? res : (res.tasks || []);
        renderHistoryTable();
    } catch {
        state.tasks = [];
        renderHistoryTable();
    }
}

function renderHistoryTable() {
    const tbody = document.getElementById('history-tbody');
    if (!tbody) return;
    const search = (document.getElementById('history-search')?.value || '').toLowerCase();
    const dateFilter = document.getElementById('history-date')?.value || '';

    let list = state.tasks;
    if (search) list = list.filter(t => (t.query || '').toLowerCase().includes(search) || (t.location || '').toLowerCase().includes(search));
    if (dateFilter) list = list.filter(t => (t.createdAt || t.created_at || '').startsWith(dateFilter));

    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="9">
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <div class="empty-state-text">No scrape history yet. Run a scrape from the Scraper tab!</div>
            </div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = list.map(t => {
        const id = t.id;
        const query = t.query || '-';
        const location = t.location || '-';
        const count = t.placeCount ?? t.place_count ?? t.itemsCount ?? t.items_count ?? 0;
        const account = t.accountEmail ?? t.account_email ?? (t.accountId ? `#${t.accountId}` : 'Direct Token');
        const createdAt = t.createdAt ?? t.created_at;
        const duration = t.durationMs ?? t.duration_ms ? `${((t.durationMs ?? t.duration_ms) / 1000).toFixed(1)}s` : '-';
        const status = t.status || 'completed';

        return `
        <tr onclick="viewPlaces(${id})" title="Click to view scraped places for this task">
            <td><code>#${id}</code></td>
            <td><strong class="text-indigo">${esc(query)}</strong></td>
            <td>${esc(location)}</td>
            <td><span class="badge badge-primary">${count} places</span></td>
            <td><span class="badge badge-neutral">${esc(truncate(account, 25))}</span></td>
            <td>${formatDate(createdAt)}</td>
            <td>${duration}</td>
            <td><span class="badge badge-${status === 'completed' ? 'success' : status === 'failed' ? 'danger' : 'warning'}">${esc(status.toUpperCase())}</span></td>
            <td style="text-align: right;" onclick="event.stopPropagation()">
                <div class="action-btns" style="justify-content: flex-end;">
                    <button class="action-btn" title="🔄 Generate Ulang File Export (Pilih Format)" onclick="openRegenerateModal(${id}, '${esc(query)}')">🔄 Re-Export</button>
                    <div class="dl-btns">
                        <button class="dl-btn dl-xlsx" title="Download Excel" onclick="downloadTaskFile(${id}, 'xlsx')">XLSX</button>
                        <button class="dl-btn dl-csv" title="Download CSV" onclick="downloadTaskFile(${id}, 'csv')">CSV</button>
                        <button class="dl-btn dl-json" title="Download JSON" onclick="downloadTaskFile(${id}, 'json')">JSON</button>
                        <button class="dl-btn dl-xml" title="Download XML" onclick="downloadTaskFile(${id}, 'xml')">XML</button>
                    </div>
                    <button class="action-btn primary" title="View Places" onclick="viewPlaces(${id})">👁</button>
                    <button class="action-btn danger" title="Delete Task" onclick="deleteTask(${id})">🗑️</button>
                </div>
            </td>
        </tr>
    `}).join('');
}

window.downloadTaskFile = function(taskId, format) {
    window.open(`${API}/api/tasks/${taskId}/download?format=${format}`, '_blank');
};

window.viewPlaces = async function(taskId) {
    try {
        const places = await api(`/api/tasks/${taskId}/places`);
        state.currentTaskPlaces = places || [];
        const task = state.tasks.find(t => t.id === taskId);
        
        setId('modal-task-title', task ? `Task #${taskId}: "${task.query}"` : `Task #${taskId}`);
        setId('task-places-count', `${places.length} places`);

        renderTaskPlacesTable(places);
        openModal('view-places-modal');

        const searchInput = document.getElementById('places-search');
        if (searchInput) {
            searchInput.value = '';
            searchInput.oninput = function() {
                const q = this.value.toLowerCase().trim();
                const filtered = state.currentTaskPlaces.filter(p => 
                    (p.title || p.name || '').toLowerCase().includes(q) ||
                    (p.address || '').toLowerCase().includes(q) ||
                    (p.category || '').toLowerCase().includes(q) ||
                    (p.phone || '').toLowerCase().includes(q)
                );
                renderTaskPlacesTable(filtered);
            };
        }
    } catch (err) {
        toast(`Failed to load places: ${err.message}`, 'error');
    }
};

function renderTaskPlacesTable(places) {
    const tbody = document.getElementById('places-tbody');
    if (!tbody) return;
    if (!places.length) {
        tbody.innerHTML = `<tr><td colspan="9"><div class="empty-state"><div class="empty-state-text">No places found in this task.</div></div></td></tr>`;
        return;
    }
    tbody.innerHTML = places.map((p, idx) => {
        let imgs = p.imageUrls || p.images || [];
        if (typeof imgs === 'string') {
            try { imgs = JSON.parse(imgs); } catch { imgs = [imgs]; }
        }
        if (!Array.isArray(imgs)) imgs = [];
        const photoCount = imgs.length;

        return `
        <tr onclick="showPlaceDetailByIndex(${idx}, 'task')">
            <td><strong>${esc(p.title || p.name || '-')}</strong></td>
            <td><span class="badge badge-primary">${esc(p.category || p.categoryName || '-')}</span></td>
            <td>${renderStars(p.rating || p.totalScore)}</td>
            <td>${p.reviewsCount ?? p.reviews_count ?? '-'}</td>
            <td>
                <span class="badge badge-${photoCount >= 3 ? 'success' : photoCount > 0 ? 'warning' : 'danger'}">
                    📷 ${photoCount} foto
                </span>
            </td>
            <td>${esc(truncate(p.address || '-', 40))}</td>
            <td>${esc(p.phone || '-')}</td>
            <td>${esc(p.price || '-')}</td>
            <td onclick="event.stopPropagation()">
                <div class="action-btns">
                    <button class="btn btn-sm btn-outline" onclick="showPlaceDetailByIndex(${idx}, 'task')">Detail →</button>
                    ${photoCount <= 1 ? `<button class="btn btn-sm btn-success" title="Ambil foto via Search Engine" onclick="enrichSinglePlacePhoto(${p.id})">⚡ Get Foto</button>` : ''}
                </div>
            </td>
        </tr>
    `}).join('');
}

window.enrichSinglePlacePhoto = async function(placeId) {
    try {
        toast(`Mencari foto untuk tempat #${placeId}...`, 'info');
        await api('/api/worker/enqueue-place', 'POST', { placeId });
        toast(`Tempat #${placeId} dimasukkan ke antrean worker foto`, 'success');
    } catch (err) {
        toast(`Gagal: ${err.message}`, 'error');
    }
};

window.deleteTask = async function(taskId) {
    if (!confirm(`Delete Task #${taskId} and all its scraped places from SQLite?`)) return;
    try {
        await api(`/api/tasks/${taskId}`, 'DELETE');
        toast(`Task #${taskId} deleted`, 'info');
        loadHistory();
        loadStats();
        loadMasterPlaces();
    } catch (err) { toast(`Failed: ${err.message}`, 'error'); }
};

window.openRegenerateModal = function(taskId, query) {
    window.__selectedRegenTaskId = taskId;
    setId('regen-task-title', `Task #${taskId} ("${query}")`);
    openModal('regenerate-export-modal');
};

document.getElementById('confirm-regen-btn')?.addEventListener('click', async () => {
    const taskId = window.__selectedRegenTaskId;
    const format = document.getElementById('regen-format-select')?.value || 'all';
    if (!taskId) return;

    try {
        toast(`Generating export files for Task #${taskId} (${format.toUpperCase()})...`, 'info');
        const res = await api(`/api/tasks/${taskId}/regenerate-export`, 'POST', { format });
        toast(`Export generated! ${res.files?.length || 0} file(s) created.`, 'success');
        closeModal('regenerate-export-modal');
        loadHistory();
        loadOutputs();
    } catch (err) {
        toast(`Failed: ${err.message}`, 'error');
    }
});

// ─── HASIL OUTPUT (TAB 3) ────────────────────────────────────────
function initOutputsActions() {
    document.getElementById('refresh-outputs-btn')?.addEventListener('click', () => {
        loadOutputs();
        toast('File outputs refreshed', 'info');
    });
}

async function loadOutputs() {
    const tbody = document.getElementById('outputs-tbody');
    if (!tbody) return;
    try {
        const files = await api('/api/outputs');
        state.outputs = Array.isArray(files) ? files : [];
        if (!state.outputs.length) {
            tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><div class="empty-state-icon">📁</div><div class="empty-state-text">Belum ada file export yang dibuat di direktori output/.</div></div></td></tr>`;
            return;
        }
        tbody.innerHTML = state.outputs.map(f => `
            <tr>
                <td><strong>${esc(f.name)}</strong></td>
                <td><span class="badge badge-${f.format === 'xlsx' ? 'success' : f.format === 'csv' ? 'primary' : 'neutral'}">${esc(f.format.toUpperCase())}</span></td>
                <td>${formatBytes(f.size)}</td>
                <td>${formatDate(f.createdAt)}</td>
                <td style="text-align: right;">
                    <a href="${f.downloadUrl}" target="_blank" class="btn btn-sm btn-primary">⬇️ Download</a>
                </td>
            </tr>
        `).join('');
    } catch {
        tbody.innerHTML = `<tr><td colspan="5"><div class="empty-state"><div class="empty-state-text">Could not load files list.</div></div></td></tr>`;
    }
}

// ─── MASTER PLACES EXPLORER ──────────────────────────────────────
function initMasterPlacesActions() {
    document.getElementById('refresh-places-btn')?.addEventListener('click', () => {
        loadMasterPlaces();
        loadCategories();
        loadTaskFilters();
        toast('Database places refreshed', 'info');
    });

    document.getElementById('enqueue-missing-photos-btn')?.addEventListener('click', async () => {
        const filter = document.getElementById('master-places-photo-filter')?.value || 'low';
        if (!confirm('Jalankan background worker untuk mencari foto otomatis bagi seluruh tempat yang belum punya foto?')) return;
        try {
            toast('Memasukkan tempat ke antrean background photo worker...', 'info');
            const res = await api('/api/worker/enqueue-all', 'POST', { filter });
            toast(`${res.count} tempat berhasil dimasukkan ke antrean worker!`, 'success');
        } catch (err) {
            toast(`Gagal: ${err.message}`, 'error');
        }
    });

    document.getElementById('enrich-task-photos-btn')?.addEventListener('click', async () => {
        const modalTitle = document.getElementById('modal-task-title')?.innerText || '';
        const match = modalTitle.match(/#(\d+)/);
        const taskId = match ? match[1] : null;
        if (!taskId) return;
        try {
            toast(`Memasukkan tempat di Task #${taskId} ke antrean worker foto...`, 'info');
            const res = await api('/api/worker/enqueue-task', 'POST', { taskId });
            toast(`${res.count} tempat Task #${taskId} dimasukkan ke antrean worker!`, 'success');
        } catch (err) {
            toast(`Gagal: ${err.message}`, 'error');
        }
    });

    // Search Category Autocomplete Dropdown
    initCategoryAutocomplete();

    // Source Task filter change
    document.getElementById('master-places-task-filter')?.addEventListener('change', () => {
        state.masterPlacesOffset = 0;
        loadMasterPlaces();
    });

    const searchInput = document.getElementById('master-places-search');
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.masterPlacesOffset = 0;
            loadMasterPlaces();
        }, 300);
    });

    document.getElementById('master-places-photo-filter')?.addEventListener('change', () => {
        state.masterPlacesOffset = 0;
        loadMasterPlaces();
    });

    document.getElementById('places-prev-btn')?.addEventListener('click', () => {
        if (state.masterPlacesOffset > 0) {
            state.masterPlacesOffset = Math.max(0, state.masterPlacesOffset - state.masterPlacesLimit);
            loadMasterPlaces();
        }
    });

    document.getElementById('places-next-btn')?.addEventListener('click', () => {
        if (state.masterPlacesOffset + state.masterPlacesLimit < state.masterPlacesTotal) {
            state.masterPlacesOffset += state.masterPlacesLimit;
            loadMasterPlaces();
        }
    });
}

function initCategoryAutocomplete() {
    const input = document.getElementById('master-places-category-input');
    const dropdown = document.getElementById('master-category-dropdown');
    if (!input || !dropdown) return;

    const renderCatDropdown = (query = '') => {
        const q = query.toLowerCase().trim();
        let matches = state.categories;
        if (q) matches = matches.filter(c => c.toLowerCase().includes(q));

        if (!matches.length && q) {
            dropdown.innerHTML = `<div class="autocomplete-item" data-value="${esc(q)}"><span>Gunakan filter: "<strong>${esc(q)}</strong>"</span></div>`;
        } else {
            dropdown.innerHTML = `<div class="autocomplete-item" data-value="all"><span>— Semua Kategori —</span></div>` +
                matches.slice(0, 50).map(c => `
                    <div class="autocomplete-item" data-value="${esc(c)}">
                        <span>${esc(c)}</span>
                    </div>
                `).join('');
        }

        dropdown.querySelectorAll('.autocomplete-item').forEach(el => {
            el.addEventListener('click', () => {
                const val = el.dataset.value;
                input.value = val === 'all' ? '' : val;
                input.dataset.selected = val;
                dropdown.classList.remove('open');
                state.masterPlacesOffset = 0;
                loadMasterPlaces();
            });
        });

        dropdown.classList.add('open');
    };

    input.addEventListener('focus', () => renderCatDropdown(input.value));
    input.addEventListener('input', () => {
        input.dataset.selected = input.value.trim() || 'all';
        renderCatDropdown(input.value);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#master-places-category-input') && !e.target.closest('#master-category-dropdown')) {
            dropdown.classList.remove('open');
        }
    });
}

async function loadCategories() {
    try {
        const cats = await api('/api/categories');
        state.categories = Array.isArray(cats) ? cats : [];
    } catch {}
}

async function loadTaskFilters() {
    try {
        const tasks = await api('/api/tasks');
        const list = Array.isArray(tasks) ? tasks : [];
        const sel = document.getElementById('master-places-task-filter');
        if (sel) {
            sel.innerHTML = '<option value="all">Semua Source Task</option>' +
                list.map(t => `<option value="${t.id}">Task #${t.id} - ${esc(t.query || '')} (${t.placeCount || 0} places)</option>`).join('');
        }
    } catch {}
}

async function loadMasterPlaces() {
    const tbody = document.getElementById('master-places-tbody');
    if (!tbody) return;

    const search = document.getElementById('master-places-search')?.value.trim() || '';
    const catInput = document.getElementById('master-places-category-input');
    const category = catInput?.dataset.selected || catInput?.value.trim() || 'all';
    const taskId = document.getElementById('master-places-task-filter')?.value || 'all';
    const photoFilter = document.getElementById('master-places-photo-filter')?.value || 'all';

    try {
        const endpoint = `/api/places?search=${encodeURIComponent(search)}&category=${encodeURIComponent(category)}&taskId=${encodeURIComponent(taskId)}&photoFilter=${encodeURIComponent(photoFilter)}&limit=${state.masterPlacesLimit}&offset=${state.masterPlacesOffset}`;

        const res = await api(endpoint);
        state.masterPlaces = res.places || [];
        state.masterPlacesTotal = res.total || 0;

        // Update pagination
        const start = state.masterPlacesTotal === 0 ? 0 : state.masterPlacesOffset + 1;
        const end = Math.min(state.masterPlacesOffset + state.masterPlacesLimit, state.masterPlacesTotal);
        setId('places-pagination-info', `Showing ${start}-${end} of ${state.masterPlacesTotal} places`);
        
        const prevBtn = document.getElementById('places-prev-btn');
        const nextBtn = document.getElementById('places-next-btn');
        if (prevBtn) prevBtn.disabled = state.masterPlacesOffset === 0;
        if (nextBtn) nextBtn.disabled = end >= state.masterPlacesTotal;

        if (!state.masterPlaces.length) {
            tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state"><div class="empty-state-icon">🗺️</div><div class="empty-state-text">No places found matching criteria.</div></div></td></tr>`;
            return;
        }

        tbody.innerHTML = state.masterPlaces.map((p, idx) => {
            let imgs = p.imageUrls || p.images || [];
            if (typeof imgs === 'string') {
                try { imgs = JSON.parse(imgs); } catch { imgs = [imgs]; }
            }
            if (!Array.isArray(imgs)) imgs = [];
            const photoCount = imgs.length;

            return `
            <tr onclick="showPlaceDetailByIndex(${idx}, 'master')">
                <td><code>#${p.id}</code></td>
                <td><strong class="text-indigo">${esc(p.title || p.name || '-')}</strong></td>
                <td><span class="badge badge-primary">${esc(p.category || '-')}</span></td>
                <td>${renderStars(p.rating || p.totalScore)}</td>
                <td>${p.reviewsCount ?? p.reviews_count ?? '-'}</td>
                <td>
                    <span class="badge badge-${photoCount >= 3 ? 'success' : photoCount > 0 ? 'warning' : 'danger'}">
                        📷 ${photoCount} foto
                    </span>
                </td>
                <td>${esc(truncate(p.address || '-', 35))}</td>
                <td>${esc(p.phone || '-')}</td>
                <td>${esc(p.price || '-')}</td>
                <td><span class="badge badge-neutral">${esc(truncate(p.taskQuery || `#${p.taskId}`, 18))}</span></td>
                <td onclick="event.stopPropagation()">
                    <div class="action-btns">
                        <button class="btn btn-sm btn-outline" onclick="showPlaceDetailByIndex(${idx}, 'master')">Detail →</button>
                        ${photoCount <= 1 ? `<button class="btn btn-sm btn-success" title="Ambil foto via Search Engine" onclick="enrichSinglePlacePhoto(${p.id})">⚡ Get Foto</button>` : ''}
                    </div>
                </td>
            </tr>
        `}).join('');
    } catch {
        tbody.innerHTML = `<tr><td colspan="11"><div class="empty-state"><div class="empty-state-text">Could not load places.</div></div></td></tr>`;
    }
}

// ─── WORKERS (TAB 4) ─────────────────────────────────────────────
function initWorkerActions() {
    document.getElementById('refresh-worker-btn')?.addEventListener('click', () => {
        loadWorkerData();
        toast('Worker data refreshed', 'info');
    });

    document.getElementById('worker-queue-filter')?.addEventListener('change', () => {
        loadWorkerQueue();
    });

    document.getElementById('clear-completed-worker-btn')?.addEventListener('click', async () => {
        try {
            const res = await api('/api/worker/clear', 'POST', { status: 'completed' });
            toast(`Dibersihkan ${res.count} antrean yang selesai`, 'info');
            loadWorkerData();
        } catch (err) {
            toast(`Gagal: ${err.message}`, 'error');
        }
    });

    document.getElementById('clear-worker-log-btn')?.addEventListener('click', () => {
        const term = document.getElementById('worker-terminal');
        if (term) term.innerHTML = '';
    });
}

async function loadWorkerData() {
    loadWorkerStats();
    loadWorkerQueue();
}

async function loadWorkerStats() {
    try {
        const stats = await api('/api/worker/stats');
        setId('worker-stat-pending', stats.pending ?? 0);
        setId('worker-stat-processing', stats.processing ?? 0);
        setId('worker-stat-completed', stats.completed ?? 0);
        setId('worker-stat-failed', stats.failed ?? 0);
        setId('worker-stat-zerophotos', stats.zeroPhotos ?? 0);
    } catch {}
}

async function loadWorkerQueue() {
    const tbody = document.getElementById('worker-queue-tbody');
    if (!tbody) return;

    const status = document.getElementById('worker-queue-filter')?.value || 'all';

    try {
        const res = await api(`/api/worker/queue?status=${status}&limit=100`);
        const items = res.items || [];

        if (!items.length) {
            tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="empty-state-icon">⚙️</div><div class="empty-state-text">Tidak ada antrean background worker.</div></div></td></tr>`;
            return;
        }

        tbody.innerHTML = items.map((q) => {
            const statusColorMap = { pending: 'warning', processing: 'primary', completed: 'success', failed: 'danger' };
            const badgeColor = statusColorMap[q.status] || 'neutral';

            return `
            <tr>
                <td><code>#${q.queueId || q.id}</code></td>
                <td><strong class="text-indigo">${esc(q.title || '-')}</strong></td>
                <td>${esc(truncate(q.address || '-', 35))}</td>
                <td><span class="badge badge-${badgeColor}">${esc((q.status || '').toUpperCase())}</span></td>
                <td>${q.attempts ?? 0}x</td>
                <td>${formatDate(q.updatedAt || q.createdAt)}</td>
            </tr>
        `}).join('');
    } catch {
        tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="empty-state-text">Gagal memuat daftar antrean worker.</div></div></td></tr>`;
    }
}

// ─── PLACE DETAIL MODAL ──────────────────────────────────────────
window.showPlaceDetailByIndex = function(index, source = 'master') {
    let place = null;
    if (source === 'master') place = state.masterPlaces[index];
    else if (source === 'task') place = state.currentTaskPlaces[index];
    else if (source === 'preview') place = window.__previewPlaces?.[index];

    if (!place) return;
    showPlaceDetail(place);
};

function showPlaceDetail(place) {
    setId('detail-place-name', place.title || place.name || 'Unnamed Place');
    setId('detail-place-category', place.category || place.categoryName || 'Business');

    const score = place.rating || place.totalScore;
    const revs = place.reviewsCount ?? place.reviews_count ?? 0;
    const ratingHtml = score ? `${renderStars(score)} <span class="text-muted">(${revs} reviews)</span>` : '-';
    document.getElementById('detail-place-rating').innerHTML = ratingHtml;

    setId('detail-place-phone', place.phone || '-');
    
    const webEl = document.getElementById('detail-place-website');
    if (place.website) {
        webEl.innerHTML = `<a href="${esc(place.website)}" target="_blank" class="text-indigo" style="text-decoration: underline;">${esc(truncate(place.website, 50))} ↗</a>`;
    } else {
        webEl.textContent = '-';
    }

    setId('detail-place-address', place.address || '-');
    setId('detail-place-price', place.price || place.priceLevel || '-');

    const lat = place.latitude || place.location?.lat;
    const lng = place.longitude || place.location?.lng;
    setId('detail-place-coords', (lat && lng) ? `${Number(lat).toFixed(6)}, ${Number(lng).toFixed(6)}` : '-');

    // Hours
    const hours = place.openingHours || place.opening_hours;
    const hoursSection = document.getElementById('detail-hours-section');
    if (hours) {
        hoursSection.style.display = 'block';
        setId('detail-place-hours', hours);
    } else {
        hoursSection.style.display = 'none';
    }

    // Description
    const desc = place.description || place.about;
    const descSection = document.getElementById('detail-desc-section');
    if (desc) {
        descSection.style.display = 'block';
        setId('detail-place-desc', typeof desc === 'object' ? JSON.stringify(desc) : desc);
    } else {
        descSection.style.display = 'none';
    }

    // Facilities
    const fac = place.facilities || place.amenities;
    const facSection = document.getElementById('detail-fac-section');
    const facContainer = document.getElementById('detail-place-facilities');
    facContainer.innerHTML = '';
    if (fac) {
        facSection.style.display = 'block';
        const facList = Array.isArray(fac) ? fac : String(fac).split(/[;,]/).map(s => s.trim()).filter(Boolean);
        if (facList.length) {
            facContainer.innerHTML = facList.map(f => `<span class="facility-tag">✔ ${esc(typeof f === 'string' ? f : f.name || JSON.stringify(f))}</span>`).join('');
        } else {
            facSection.style.display = 'none';
        }
    } else {
        facSection.style.display = 'none';
    }

    // Photos
    const photosContainer = document.getElementById('detail-place-photos');
    photosContainer.innerHTML = '<div class="empty-state-text" style="grid-column: 1/-1; padding: 1.5rem 0;">Memuat foto...</div>';
    
    let images = place.imageUrls || place.images || place.imageUrl || place.image_urls;
    if (typeof images === 'string') {
        if (images.startsWith('http')) images = [images];
        else {
            try { images = JSON.parse(images); } catch { images = [images]; }
        }
    }
    if (!Array.isArray(images)) images = [];
    images = images.filter(url => typeof url === 'string' && url.startsWith('http'));

    const renderPhotos = (photosList) => {
        if (photosList.length > 0) {
            photosContainer.innerHTML = photosList.map(imgUrl => `
                <a href="${esc(imgUrl)}" target="_blank" class="photo-thumb-link" rel="noreferrer">
                    <img src="${esc(imgUrl)}" class="photo-thumb" alt="Place photo" loading="lazy" referrerpolicy="no-referrer" onerror="if(!this.dataset.retried){this.dataset.retried='1';this.src='/api/proxy-image?url='+encodeURIComponent(this.src);}else{this.parentElement.style.display='none';}">
                </a>
            `).join('');
        } else {
            photosContainer.innerHTML = `<div class="empty-state-text" style="grid-column: 1/-1; padding: 2rem 0;">Tidak ada foto untuk tempat ini</div>`;
        }
    };

    if (images.length > 0) {
        renderPhotos(images);
    } else {
        // Automatically search, stream, and save photos to database
        const placeQuery = `${place.title || place.name || ''} ${place.address || ''}`.trim();
        const placeIdParam = place.id ? `&placeId=${place.id}` : '';
        const googlePlaceIdParam = place.placeId || place.place_id ? `&googlePlaceId=${encodeURIComponent(place.placeId || place.place_id)}` : '';
        if (placeQuery) {
            api(`/api/places/photos?q=${encodeURIComponent(placeQuery)}${placeIdParam}${googlePlaceIdParam}&limit=10`)
                .then(res => {
                    const fetchedPhotos = res.photos || [];
                    if (fetchedPhotos.length) {
                        place.imageUrls = fetchedPhotos; // cache locally
                        renderPhotos(fetchedPhotos);
                    } else {
                        renderPhotos([]);
                    }
                })
                .catch(() => renderPhotos([]));
        } else {
            renderPhotos([]);
        }
    }

    // Maps link
    const mapsLink = document.getElementById('detail-maps-link');
    const mapsUrl = place.googleMapsUrl || place.google_maps_url || place.url || `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent((place.title || '') + ' ' + (place.address || ''))}`;
    mapsLink.href = mapsUrl;

    openModal('place-detail-modal');
}

// ─── LIVE CONSOLE (TAB 5) ────────────────────────────────────────
function initLogConsole() {
    document.getElementById('clear-console-btn')?.addEventListener('click', () => {
        document.getElementById('console-terminal').innerHTML = '';
        state.logLines = [];
    });

    document.getElementById('download-logs-btn')?.addEventListener('click', () => {
        const text = state.logLines.join('\n');
        const blob = new Blob([text], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `apify_gmaps_logs_${Date.now()}.txt`;
        a.click();
        URL.revokeObjectURL(a.href);
    });

    document.getElementById('log-level-filter')?.addEventListener('change', () => {
        const level = document.getElementById('log-level-filter').value;
        const term = document.getElementById('console-terminal');
        [...term.querySelectorAll('.log-line')].forEach(el => {
            el.style.display = (level === 'all' || el.dataset.level === level) ? '' : 'none';
        });
    });
}

// ─── SSE ─────────────────────────────────────────────────────────
function connectSSE() {
    if (state.sseSource) state.sseSource.close();
    try {
        const es = new EventSource(`${API}/api/logs/stream`);
        state.sseSource = es;
        es.onmessage = (e) => {
            try {
                const data = JSON.parse(e.data);
                const level = data.level || 'info';
                const msg = data.message || data.msg || e.data;
                const channel = data.channel || 'scraper';

                // Route logs to appropriate terminals
                if (channel === 'worker') {
                    appendLog('worker-terminal', level, msg);
                    appendLog('console-terminal', level, `[WORKER] ${msg}`);
                } else {
                    appendLog('scrape-terminal', level, msg);
                    appendLog('console-terminal', level, msg);
                }

                state.logLines.push(`[${channel.toUpperCase()}] [${level.toUpperCase()}] ${msg}`);
            } catch {
                appendLog('console-terminal', 'info', e.data);
            }
        };
        es.onerror = () => {
            es.close();
            setTimeout(connectSSE, 5000);
        };
    } catch {}
}

// ─── MODALS ──────────────────────────────────────────────────────
function initModals() {
    document.querySelectorAll('.modal-close, [data-modal]').forEach(el => {
        el.addEventListener('click', () => {
            const id = el.dataset.modal;
            if (id) closeModal(id);
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal(modal.id);
        });
    });
}

function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('open');
}
function closeModal(id) {
    const el = document.getElementById(id);
    if (el) el.classList.remove('open');
}

// ─── TOAST ───────────────────────────────────────────────────────
function toast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const icons = { success: '✅', error: '❌', warn: '⚠️', info: 'ℹ️' };
    const el = document.createElement('div');
    el.className = `toast toast-${type}`;
    el.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${esc(message)}</span>`;
    container.appendChild(el);
    setTimeout(() => {
        el.classList.add('toast-exit');
        el.addEventListener('animationend', () => el.remove());
    }, 3500);
}

// ─── LOG APPEND ──────────────────────────────────────────────────
function appendLog(termId, level, message) {
    const term = document.getElementById(termId);
    if (!term) return;
    const time = new Date().toLocaleTimeString('id-ID');
    const line = document.createElement('div');
    line.className = 'log-line';
    line.dataset.level = level;
    line.innerHTML = `<span class="log-time">${time}</span><span class="log-${level}">[${level.toUpperCase()}]</span> ${esc(message)}`;
    term.appendChild(line);

    const autoScrollId = termId === 'console-terminal' ? 'console-auto-scroll' : 'auto-scroll';
    const autoScroll = document.getElementById(autoScrollId);
    if (!autoScroll || autoScroll.checked) {
        term.scrollTop = term.scrollHeight;
    }
}

// ─── API CLIENT ──────────────────────────────────────────────────
async function api(path, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(`${API}${path}`, opts);
    if (!res.ok) {
        const err = await res.json().catch(() => ({ message: res.statusText }));
        throw new Error(err.message || err.error || res.statusText);
    }
    return res.json();
}

// ─── HELPERS ─────────────────────────────────────────────────────
function setId(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

function esc(s) {
    if (!s) return '';
    const d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
}

function maskToken(t) {
    if (!t || t.length < 10) return t || '—';
    return t.slice(0, 8) + '••••••••' + t.slice(-4);
}

function truncate(s, n) {
    return s && s.length > n ? s.slice(0, n) + '…' : (s || '');
}

function formatNum(n) {
    return Number(n).toLocaleString('id-ID');
}

function formatBytes(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function formatDate(d) {
    if (!d) return '-';
    try { return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }
    catch { return d; }
}

function renderStars(score) {
    const n = Math.round(Number(score) || 0);
    if (!n) return '<span class="stars-dim">—</span>';
    return '<span class="stars">' + '★'.repeat(Math.min(n, 5)) + '</span>' +
           '<span class="stars-dim">' + '★'.repeat(Math.max(0, 5 - n)) + '</span>' +
           ` <small style="color:var(--text-muted); font-weight:600;">${Number(score).toFixed(1)}</small>`;
}

function statusColor(s) {
    const map = { active: 'success', limited: 'warning', invalid: 'danger', disabled: 'neutral' };
    return map[s] || 'neutral';
}
