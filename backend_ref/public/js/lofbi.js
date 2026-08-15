/**
 * LOFBI — Custom JavaScript
 * Synchronized with Hagi901/lofbi-api Backend
 * 
 * Handles: Sidebar toggle, Real-time clock, Chart.js init,
 *          State switcher, API Synchronization, Keyboard nav
 */

'use strict';

/* =============================================
   1. DOM READY
   ============================================= */
document.addEventListener('DOMContentLoaded', function () {
    initClock();
    initSidebar();
    initStateSwitcher();
    initCharts();
    initTooltips();
    simulatePageLoad();
    syncWithBackendApi();
});

/* =============================================
   2. REAL-TIME CLOCK
   ============================================= */
function initClock() {
    const clockEl = document.getElementById('topbar-clock');
    if (!clockEl) return;

    function updateClock() {
        const now = new Date();
        const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
        const day   = days[now.getDay()];
        const date  = now.getDate();
        const month = months[now.getMonth()];
        const year  = now.getFullYear();
        const time  = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
        clockEl.textContent = `${day}, ${date} ${month} ${year} — ${time}`;
    }

    updateClock();
    setInterval(updateClock, 1000);
}

/* =============================================
   3. SIDEBAR TOGGLE
   ============================================= */
function initSidebar() {
    const sidebar       = document.getElementById('appSidebar');
    const appMain       = document.getElementById('appMain');
    const toggleBtn     = document.getElementById('sidebarToggle');
    const overlay       = document.getElementById('sidebarOverlay');
    const isMobile      = () => window.innerWidth < 768;

    if (!sidebar) return;

    function openMobileSidebar() {
        sidebar.classList.add('mobile-open');
        overlay && overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        sidebar.classList.remove('mobile-open');
        overlay && overlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function toggleDesktopSidebar() {
        const collapsed = sidebar.classList.toggle('collapsed');
        appMain && appMain.classList.toggle('sidebar-collapsed', collapsed);
        localStorage.setItem('lofbi_sidebar_collapsed', collapsed ? '1' : '0');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            if (isMobile()) {
                sidebar.classList.contains('mobile-open') ? closeMobileSidebar() : openMobileSidebar();
            } else {
                toggleDesktopSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }

    if (!isMobile()) {
        const savedState = localStorage.getItem('lofbi_sidebar_collapsed');
        if (savedState === '1') {
            sidebar.classList.add('collapsed');
            appMain && appMain.classList.add('sidebar-collapsed');
        }
    }

    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (!isMobile()) {
                closeMobileSidebar();
            }
        }, 200);
    });
}

/* =============================================
   4. STATE SWITCHER (Demo Tool)
   ============================================= */
function initStateSwitcher() {
    const btns = document.querySelectorAll('[data-state]');
    const successContent  = document.getElementById('dashboardContent');
    const loadingContent  = document.getElementById('stateLoading');
    const emptyContent    = document.getElementById('stateEmpty');
    const errorContent    = document.getElementById('stateError');

    if (!btns.length) return;

    const allStates = [successContent, loadingContent, emptyContent, errorContent].filter(Boolean);

    function showState(targetState) {
        allStates.forEach(el => {
            el.style.display = 'none';
        });

        if (targetState) {
            targetState.style.display = '';
            targetState.classList.add('fade-in');
            setTimeout(() => targetState.classList.remove('fade-in'), 600);
        }

        btns.forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`[data-state="${targetState ? targetState.id : 'success'}"]`);
        if (activeBtn) activeBtn.classList.add('active');

        if (targetState === successContent) {
            setTimeout(() => {
                if (window.chartTrendAsset) window.chartTrendAsset.update();
                if (window.chartPersediaan) window.chartPersediaan.update();
            }, 100);
        }
    }

    btns.forEach(btn => {
        btn.addEventListener('click', function () {
            const stateId = this.dataset.state;
            let target;
            switch (stateId) {
                case 'success': target = successContent;  break;
                case 'loading': target = loadingContent;  break;
                case 'empty':   target = emptyContent;    break;
                case 'error':   target = errorContent;    break;
            }
            showState(target);
        });

        btn.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    const retryBtn = document.getElementById('btnRetry');
    if (retryBtn) {
        retryBtn.addEventListener('click', function () {
            const successBtn = document.querySelector('[data-state="success"]');
            if (successBtn) successBtn.click();
        });
    }

    const emptyCta = document.getElementById('btnEmptyCta');
    if (emptyCta) {
        emptyCta.addEventListener('click', function () {
            alert('Redirect ke halaman Tambah Barang (Route backend: /api/aset)');
        });
    }
}

/* =============================================
   5. SIMULATE PAGE LOAD
   ============================================= */
function simulatePageLoad() {
    const skeleton = document.getElementById('skeletonLoader');
    const content  = document.getElementById('dashboardContent');
    if (!skeleton || !content) return;

    skeleton.style.display = '';
    content.style.display = 'none';

    setTimeout(function () {
        skeleton.style.display = 'none';
        content.style.display  = '';
        content.classList.add('fade-in');
        setTimeout(() => content.classList.remove('fade-in'), 600);
    }, 1200);
}

/* =============================================
   6. API SYNCHRONIZATION WITH lofbi-api BACKEND
   ============================================= */
async function syncWithBackendApi() {
    if (typeof LOFBI_API === 'undefined') return;

    try {
        const apiStatusBadge = document.getElementById('apiStatusBadge');
        const summary = await LOFBI_API.getDashboardSummary();
        
        console.log('[LOFBI API Bridge] Connected to backend API successfully! Summary data:', summary);
        
        if (apiStatusBadge) {
            apiStatusBadge.textContent = 'API Terhubung';
            apiStatusBadge.className = 'table-badge success';
        }

        // Update Stat Cards with live backend values if present
        if (summary.total_aset !== undefined) {
            updateStatValue('valTotalAsset', summary.total_aset);
        }
        if (summary.total_nilai_buku !== undefined) {
            const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(summary.total_nilai_buku);
            updateStatElement('valNilaiBuku', formatted);
        }
        if (summary.alert_barang_rusak !== undefined) {
            updateStatValue('valAssetRusak', summary.alert_barang_rusak);
        }
        if (summary.alert_stok_menipis !== undefined) {
            updateStatValue('valStokMenipis', summary.alert_stok_menipis);
        }
        if (summary.alert_pengajuan_menunggu !== undefined) {
            updateStatValue('valPendingApproval', summary.alert_pengajuan_menunggu);
        }

    } catch (err) {
        console.info('[LOFBI API Bridge] Running in offline demo mode with synchronized backend dummy data.');
    }
}

function updateStatValue(elementId, value) {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.textContent = Number(value).toLocaleString('id-ID');
    el.dataset.value = value;
}

function updateStatElement(elementId, text) {
    const el = document.getElementById(elementId);
    if (el) el.textContent = text;
}

/* =============================================
   7. CHART.JS INITIALIZATION
   ============================================= */
function initCharts() {
    initTrendAssetChart();
    initPersediaanChart();
}

/* --- 7a. Line Chart: Tren Asset (Kondisi: Baik vs Rusak) --- */
function initTrendAssetChart() {
    const ctx = document.getElementById('chartTrendAsset');
    if (!ctx) return;

    // Backend models: Aset (kondisi: 'baik', 'rusak_ringan', 'rusak_berat')
    const labels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];
    const dataAssetBaik = [2580, 2612, 2640, 2658, 2675, 2690, 2702, 2710, 2718, 2730, 2741, 2702];
    const dataAssetRusak = [148, 152, 149, 155, 151, 148, 145, 143, 147, 144, 142, 145];

    window.chartTrendAsset = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Asset Baik',
                    data: dataAssetBaik,
                    borderColor: '#1a9e6b',
                    backgroundColor: 'rgba(26,158,107,.1)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1a9e6b',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Asset Rusak (Ringan/Berat)',
                    data: dataAssetRusak,
                    borderColor: '#d93025',
                    backgroundColor: 'rgba(217,48,37,.07)',
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#d93025',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { family: "'Inter', sans-serif", size: 12 },
                        padding: 16,
                        usePointStyle: true,
                        pointStyleWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,36,71,.92)',
                    titleFont: { family: "'Inter', sans-serif", size: 12, weight: '600' },
                    bodyFont:  { family: "'Inter', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString('id-ID')} unit`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11.5 },
                        color: '#5a6a80'
                    },
                    border: { display: false }
                },
                y: {
                    grid: { color: '#e8edf4', drawBorder: false },
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11.5 },
                        color: '#5a6a80',
                        callback: v => v.toLocaleString('id-ID')
                    },
                    border: { display: false, dash: [4,4] }
                }
            }
        }
    });
}

/* --- 7b. Doughnut Chart: Distribusi Persediaan per Kategori --- */
function initPersediaanChart() {
    const ctx = document.getElementById('chartPersediaan');
    if (!ctx) return;

    // Backend models: Kategori (ATK, Rumah Tangga, Elektronik, Furnitur)
    const kategori = ['ATK (Alat Tulis Kantor)', 'Rumah Tangga', 'Perlengkapan Pelabuhan', 'Spare Part', 'Bahan Kimia', 'Lainnya'];
    const jumlah   = [3840, 2105, 4210, 2680, 895, 662];

    const kemenhubColors = [
        '#1e4fa0', // Kemenhub Blue
        '#f5a800', // Kemenhub Gold
        '#1a9e6b', // Success Green
        '#0e8a8a', // Teal
        '#d93025', // Danger Red
        '#94a3b8', // Grey
    ];

    window.chartPersediaan = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: kategori,
            datasets: [{
                data: jumlah,
                backgroundColor: kemenhubColors,
                hoverBackgroundColor: kemenhubColors.map(c => c + 'cc'),
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: "'Inter', sans-serif", size: 11.5 },
                        padding: 12,
                        usePointStyle: true,
                        pointStyleWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,36,71,.92)',
                    titleFont: { family: "'Inter', sans-serif", size: 12, weight: '600' },
                    bodyFont:  { family: "'Inter', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct   = ((ctx.raw / total) * 100).toFixed(1);
                            return ` ${ctx.label}: ${ctx.raw.toLocaleString('id-ID')} unit (${pct}%)`;
                        }
                    }
                }
            }
        }
    });
}

/* =============================================
   8. TOOLTIPS
   ============================================= */
function initTooltips() {
    if (typeof bootstrap === 'undefined') return;
    const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover focus' }));
}

/* =============================================
   9. NUMBER COUNT-UP ANIMATION
   ============================================= */
function countUp(el, target, duration) {
    let start = 0;
    const step = duration / 60;
    const increment = target / (duration / step);
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            start = target;
            clearInterval(timer);
        }
        el.textContent = Math.floor(start).toLocaleString('id-ID');
    }, step);
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        document.querySelectorAll('.stat-value-animate').forEach(el => {
            const raw = parseInt(el.dataset.value || el.textContent.replace(/\D/g, ''), 10);
            if (!isNaN(raw)) countUp(el, raw, 1000);
        });
    }, 1300);
});
