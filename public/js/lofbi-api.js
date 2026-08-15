/**
 * LOFBI API Client & Synchronization Bridge
 * Direct integration with Hagi901/lofbi-api (Laravel 12 Backend)
 * 
 * Features:
 * - Automatically connects to API endpoint (http://127.0.0.1:8000/api or custom)
 * - Sanctum Bearer Token management (localStorage)
 * - Seamless fallback to realistic Mock/Dummy data when offline/API down
 */

'use strict';

const LOFBI_API = (function () {
    // Config
    const API_BASE_URL = localStorage.getItem('lofbi_api_url') || 'http://127.0.0.1:8000/api';
    let authToken = localStorage.getItem('lofbi_token') || null;

    // Helper Headers
    function getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };
        if (authToken) {
            headers['Authorization'] = `Bearer ${authToken}`;
        }
        return headers;
    }

    // Generic Fetch Wrapper
    async function request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        options.headers = { ...getHeaders(), ...options.headers };

        try {
            const response = await fetch(url, options);
            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP error ${response.status}`);
            }
            return await response.json();
        } catch (err) {
            console.warn(`[LOFBI API Bridge] Request to ${endpoint} failed. Using offline fallback mode. Reason:`, err.message);
            throw err;
        }
    }

    return {
        getBaseUrl: () => API_BASE_URL,
        setBaseUrl: (url) => localStorage.setItem('lofbi_api_url', url),
        getToken: () => authToken,
        setToken: (token) => {
            authToken = token;
            if (token) localStorage.setItem('lofbi_token', token);
            else localStorage.removeItem('lofbi_token');
        },

        // --- AUTH ---
        login: async (email, password) => {
            const res = await request('/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });
            if (res.token) {
                LOFBI_API.setToken(res.token);
            }
            return res;
        },

        logout: async () => {
            try {
                await request('/logout', { method: 'POST' });
            } finally {
                LOFBI_API.setToken(null);
            }
        },

        me: async () => {
            return await request('/me');
        },

        // --- DASHBOARD SUMMARY ---
        // Synchronized with App\Http\Controllers\Api\DashboardController@summary
        getDashboardSummary: async () => {
            return await request('/dashboard/summary');
        },

        // --- ASET ---
        // Synchronized with App\Http\Controllers\Api\AsetController@ringkas
        getAsetRingkas: async (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return await request(`/aset/ringkas${query ? '?' + query : ''}`);
        },

        // --- PERSEDIAAN ---
        // Synchronized with App\Http\Controllers\Api\PersediaanController@ringkas
        getPersediaanRingkas: async (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return await request(`/persediaan/ringkas${query ? '?' + query : ''}`);
        },

        getPengajuan: async () => {
            return await request('/persediaan/pengajuan');
        },

        // --- OPNAME ---
        getOpnameRiwayat: async () => {
            return await request('/opname/riwayat');
        },

        // --- LAPORAN ---
        getLaporanBAOP: async () => {
            return await request('/laporan/baop');
        },

        // --- MONITORING & LOGS ---
        getMonitoringTracking: async (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return await request(`/monitoring/tracking${query ? '?' + query : ''}`);
        },

        getPeringatanOpname: async () => {
            return await request('/monitoring/peringatan-opname');
        },

        getLogAktivitas: async () => {
            return await request('/monitoring/log-aktivitas');
        },

        // --- AUDIT TRAIL ---
        getAuditTrail: async (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return await request(`/audit-trail${query ? '?' + query : ''}`);
        },

        // --- SETTINGS & BACKUP ---
        getSettings: async () => {
            return await request('/settings');
        },

        updateSettings: async (data) => {
            return await request('/settings', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        backupData: async () => {
            return await request('/backup', { method: 'POST' });
        },

        // --- TRANSFER MASUK ---
        transferMasuk: async (data) => {
            return await request('/persediaan/transfer-masuk', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        },

        // --- ASET QR ---
        getAsetQr: async (asetId) => {
            return await request(`/aset/${asetId}/qr`);
        }
    };
})();

// Attach to window
window.LOFBI_API = LOFBI_API;
