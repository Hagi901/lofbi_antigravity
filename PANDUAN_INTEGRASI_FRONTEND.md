# 📘 Panduan Integrasi Frontend & Backend LOFBI
### Khusus Pengembang Frontend (Blade / JavaScript) — KSOP Kelas I Banten

Dokumen ini disusun untuk memudahkan tim Frontend dalam menghubungkan antarmuka UI (Blade Templates / JavaScript) dengan **REST API Backend LOFBI** (`lofbi_antigravity`).

---

## 🌐 1. Informasi Konfigurasi Server

- **Base API URL:** `http://127.0.0.1:8000/api`
- **Tipe Autentikasi:** Bearer Token via **Laravel Sanctum**
- **Format Data:** JSON (`Content-Type: application/json` & `Accept: application/json`)

---

## 🔑 2. Akun Demo untuk Pengujian (5 Role)

Semua akun menggunakan password: **`password`**

| Role | Email Login | Hak Akses Utama |
|---|---|---|
| **Administrator** | `admin@lofbi.test` | Akses penuh CRUD seluruh modul & setting |
| **Operator** | `operator@lofbi.test` | Input Aset, Catat Barang Masuk, & Monitoring |
| **Validator** | `validator@lofbi.test` | Verifikasi Aset, Validasi Opname, & Approval |
| **Viewer** | `viewer@lofbi.test` | Hanya melihat data (*read-only*) |
| **Pimpinan** | `pimpinan@lofbi.test` | Monitoring laporan, audit trail, & approval |

---

## 🛠️ 3. Helper JavaScript Siap Pakai (`api-client.js`)

Sertakan script berikut di layout utama Anda (`resources/views/layouts/app.blade.php` atau sejenisnya):

```javascript
// Konfigurasi API Client
const API_BASE_URL = 'http://127.0.0.1:8000/api';

async function apiRequest(endpoint, method = 'GET', body = null) {
    const token = localStorage.getItem('lofbi_token');
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const options = { method, headers };
    if (body) {
        options.body = JSON.stringify(body);
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || `HTTP Error ${response.status}`);
    }
    return data;
}

// Global LOFBI API Service
window.LofbiApi = {
    // 1. Auth
    login: async (email, password) => {
        const res = await apiRequest('/login', 'POST', { email, password });
        if (res.token) localStorage.setItem('lofbi_token', res.token);
        return res;
    },
    logout: async () => {
        try { await apiRequest('/logout', 'POST'); }
        finally { localStorage.removeItem('lofbi_token'); }
    },
    me: () => apiRequest('/me'),

    // 2. Dashboard
    getDashboardSummary: () => apiRequest('/dashboard/summary'),

    // 3. Aset
    getAsetRingkas: (params = '') => apiRequest(`/aset/ringkas${params}`),
    getAsetUnits: (jenisBarangId) => apiRequest(`/aset/jenis/${jenisBarangId}/unit`),
    createAset: (payload) => apiRequest('/aset', 'POST', payload),
    getAsetQr: (asetId) => apiRequest(`/aset/${asetId}/qr`),
    getAsetRiwayat: (asetId) => apiRequest(`/aset/${asetId}/riwayat`),

    // 4. Persediaan & FIFO
    getPersediaanRingkas: () => apiRequest('/persediaan/ringkas'),
    getPersediaanDetail: (jenisId) => apiRequest(`/persediaan/jenis/${jenisId}/detail`),
    catatBarangMasuk: (persediaanId, payload) => apiRequest(`/persediaan/${persediaanId}/barang-masuk`, 'POST', payload),
    ajukanBarangKeluar: (persediaanId, payload) => apiRequest(`/persediaan/${persediaanId}/pengajuan-keluar`, 'POST', payload),
    transferMasuk: (payload) => apiRequest('/persediaan/transfer-masuk', 'POST', payload),
    getPengajuanList: (status = '') => apiRequest(`/persediaan/pengajuan${status ? '?status=' + status : ''}`),
    setujuiPengajuan: (transaksiId) => apiRequest(`/persediaan/pengajuan/${transaksiId}/setujui`, 'POST'),
    tolakPengajuan: (transaksiId, catatan) => apiRequest(`/persediaan/pengajuan/${transaksiId}/tolak`, 'POST', { catatan_penolakan: catatan }),

    // 5. Opname Fisik
    getOpnameRuangan: (ruanganId) => apiRequest(`/opname/ruangan/${ruanganId}`),
    simpanOpname: (payload) => apiRequest('/opname', 'POST', payload),
    getRiwayatOpname: () => apiRequest('/opname/riwayat'),

    // 6. Monitoring & Audit
    getTrackingAset: () => apiRequest('/monitoring/tracking'),
    getPeringatanOpname: () => apiRequest('/monitoring/peringatan-opname'),
    getAuditTrail: (params = '') => apiRequest(`/audit-trail${params}`),

    // 7. Laporan
    getLaporanBAOP: () => apiRequest('/laporan/baop'),
    getLaporanDBR: (ruanganId = '') => apiRequest(`/laporan/dbr${ruanganId ? '?ruangan_id=' + ruanganId : ''}`),
    getLaporanNilaiBuku: () => apiRequest('/laporan/nilai-buku'),
    getExportUrl: (jenis = 'dbr', format = 'csv') => `${API_BASE_URL}/laporan/export?jenis=${jenis}&format=${format}`,

    // 8. Settings & Master
    getSettings: () => apiRequest('/settings'),
    saveSettings: (payload) => apiRequest('/settings', 'POST', payload),
    backupData: () => apiRequest('/backup', 'POST'),
    getRuanganList: () => apiRequest('/ruangan'),
    getKategoriList: () => apiRequest('/kategori'),
};
```

---

## 🗺️ 4. Panduan Integrasi per Halaman Blade

### A. Halaman Login (`login.blade.php`)
Ketika user menekan tombol login:
```javascript
document.getElementById('formLogin').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const res = await LofbiApi.login(email, password);
        
        // Simpan user role dan redirect
        localStorage.setItem('user_role', res.user.role);
        window.location.href = '/dashboard';
    } catch (err) {
        alert('Login gagal: ' + err.message);
    }
});
```

---

### B. Halaman Dashboard (`dashboard.blade.php`)
Mengisi nilai kartu statistik dan alert secara dinamis:
```javascript
async function loadDashboard() {
    const data = await LofbiApi.getDashboardSummary();
    
    document.getElementById('totalAset').textContent = Number(data.total_aset).toLocaleString('id-ID');
    document.getElementById('totalNilaiBuku').textContent = 'Rp ' + Number(data.total_nilai_buku).toLocaleString('id-ID');
    document.getElementById('stokMenipis').textContent = data.alert_stok_menipis;
    document.getElementById('pengajuanMenunggu').textContent = data.alert_pengajuan_menunggu;
    document.getElementById('barangRusak').textContent = data.alert_barang_rusak;
}
```

---

### C. Halaman Aset (`assets/`)
1. **Mengambil Daftar Aset:**
   ```javascript
   const res = await LofbiApi.getAsetRingkas();
   // res.data berisi array kelompok aset
   ```
2. **Form Tambah Aset Baru:**
   ```javascript
   const payload = {
       kode_aset: 'ELK-LAP-005',
       jenis_barang_id: 1,
       sub_kategori: 'Elektronik',
       merk: 'Lenovo',
       model: 'ThinkPad E14',
       kondisi: 'baik', // baik | rusak_ringan | rusak_berat
       ruangan_id: 1,
       nilai_perolehan: 12500000,
       tanggal_perolehan: '2026-08-18',
       masa_manfaat: 4, // dalam tahun
       metode_penyusutan: 'Garis Lurus' // 'Garis Lurus' | 'Saldo Menurun'
   };
   await LofbiApi.createAset(payload);
   ```

---

### D. Halaman Persediaan (`inventory.blade.php`)
1. **Catat Barang Masuk (Batch FIFO):**
   ```javascript
   const payload = {
       no_referensi: 'REF-2026-099',
       no_faktur: 'INV-2026-888',
       nota_dinas: 'ND/2026/08',
       supplier: 'PT Sinar Dunia',
       tanggal: '2026-08-18',
       jumlah: 50,
       harga_satuan: 45000
   };
   await LofbiApi.catatBarangMasuk(persediaanId, payload);
   ```
2. **Ajukan Barang Keluar:**
   ```javascript
   await LofbiApi.ajukanBarangKeluar(persediaanId, {
       jumlah: 10,
       tanggal: '2026-08-18',
       unit_kerja_penerima: 'Seksi Kepegawaian'
   });
   ```
3. **Approval Kasubbag / Validator / Pimpinan:**
   ```javascript
   // Setujui (Pemotongan otomatis FIFO dari batch tertua)
   await LofbiApi.setujuiPengajuan(transaksiId);

   // Tolak
   await LofbiApi.tolakPengajuan(transaksiId, 'Stok fisik dialokasikan untuk operasional darurat.');
   ```

---

### E. Halaman Opname Fisik (`opname.blade.php` & `opname_create.blade.php`)
1. **Pilih Ruangan & Ambil Checklist Barang:**
   ```javascript
   const checklist = await LofbiApi.getOpnameRuangan(ruanganId);
   // checklist.aset -> daftar unit aset di ruangan tersebut
   // checklist.persediaan -> daftar persediaan di ruangan tersebut
   ```
2. **Simpan Sesi Opname Fisik:**
   ```javascript
   const payload = {
       ruangan_id: 1,
       tanggal: '2026-08-18',
       status: 'selesai',
       details: [
           { aset_id: 1, kondisi_fisik: 'baik', catatan: 'Sesuai' },
           { persediaan_id: 1, jumlah_fisik: 48, catatan: 'Selisih 2 pcs rusak' }
       ]
   };
   await LofbiApi.simpanOpname(payload);
   ```

---

### F. Halaman Laporan & Download (`reports.blade.php` & `download.blade.php`)
Untuk tombol unduh CSV/Excel:
```html
<a href="http://127.0.0.1:8000/api/laporan/export?jenis=dbr&format=csv" class="btn btn-primary">
    <i class="bi bi-download"></i> Unduh Laporan DBR (CSV)
</a>
<a href="http://127.0.0.1:8000/api/laporan/export?jenis=nilai-buku&format=csv" class="btn btn-success">
    <i class="bi bi-download"></i> Unduh Rekap Nilai Buku (CSV)
</a>
```

---

## 💡 5. Tips Penanganan Error & Notifikasi

Gunakan blok `try...catch` pada setiap event listener form:

```javascript
try {
    // Panggil API
    await LofbiApi.createAset(formData);
    // Tampilkan notifikasi sukses
    alert('Data berhasil disimpan!');
    location.reload();
} catch (error) {
    // Tampilkan pesan error validasi dari Laravel
    alert('Terjadi kesalahan: ' + error.message);
}
```
