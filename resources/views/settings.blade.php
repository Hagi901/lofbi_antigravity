@extends('layouts.app')

@section('page_title', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <!-- Menu Tab Sebelah Kiri -->
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active text-start fw-bold mb-2 py-3 px-3 rounded-3" id="v-pills-instansi-tab" data-bs-toggle="pill" data-bs-target="#v-pills-instansi" type="button" role="tab">
                        <i class="fa-solid fa-building-flag me-2 w-20px text-center"></i> Profil Instansi
                    </button>
                    <button class="nav-link text-start fw-bold mb-2 py-3 px-3 rounded-3" id="v-pills-notif-tab" data-bs-toggle="pill" data-bs-target="#v-pills-notif" type="button" role="tab">
                        <i class="fa-solid fa-bell me-2 w-20px text-center"></i> Notifikasi & Peringatan
                    </button>
                    <button class="nav-link text-start fw-bold py-3 px-3 rounded-3" id="v-pills-backup-tab" data-bs-toggle="pill" data-bs-target="#v-pills-backup" type="button" role="tab">
                        <i class="fa-solid fa-database me-2 w-20px text-center"></i> Pencadangan Data (Backup)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Konten Sebelah Kanan -->
    <div class="col-md-9 mb-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4 pt-4 tab-content" id="v-pills-tabContent">
                
                <!-- Tab 1: Profil Instansi -->
                <div class="tab-pane fade show active" id="v-pills-instansi" role="tabpanel">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fa-solid fa-building-flag text-primary me-2"></i>Informasi Instansi</h5>
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label small fw-bold text-secondary">Nama Aplikasi</label>
                                <input type="text" name="nama_aplikasi" class="form-control border-0 shadow-sm bg-light fw-bold" value="{{ $settings['nama_aplikasi'] ?? 'LOFBI (Layanan Operasional Fisik Barang & Inventaris)' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Nama Instansi Induk</label>
                                <input type="text" name="nama_ksop" class="form-control border-0 shadow-sm bg-light fw-bold" value="{{ $settings['nama_ksop'] ?? 'KSOP Kelas I Banten' }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Alamat Lengkap</label>
                            <textarea name="alamat_instansi" class="form-control border-0 shadow-sm bg-light" rows="3">{{ $settings['alamat_instansi'] ?? 'Jl. Raya Pelabuhan No. 1, Banten' }}</textarea>
                        </div>
                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 2: Notifikasi -->
                <div class="tab-pane fade" id="v-pills-notif" role="tabpanel">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fa-solid fa-bell text-warning me-2"></i>Preferensi Notifikasi</h5>
                    
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                            <div>
                                <h6 class="fw-bold mb-1">Peringatan Stok Menipis</h6>
                                <small class="text-muted">Kirim peringatan jika sisa stok di bawah batas aman (FIFO).</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="peringatan_stok" value="0">
                                <input class="form-check-input" type="checkbox" name="peringatan_stok" value="1" role="switch" id="switch1" {{ ($settings['peringatan_stok'] ?? '1') == '1' ? 'checked' : '' }} style="transform: scale(1.3);">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                            <div>
                                <h6 class="fw-bold mb-1">Notifikasi Opname Bulanan</h6>
                                <small class="text-muted">Ingatkan operator untuk melakukan jadwal opname setiap akhir bulan.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="notif_opname" value="0">
                                <input class="form-check-input" type="checkbox" name="notif_opname" value="1" role="switch" id="switch2" {{ ($settings['notif_opname'] ?? '1') == '1' ? 'checked' : '' }} style="transform: scale(1.3);">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3">
                            <div>
                                <h6 class="fw-bold mb-1">Laporan Aktivitas Harian</h6>
                                <small class="text-muted">Kirim rekap barang masuk dan keluar harian.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="laporan_harian" value="0">
                                <input class="form-check-input" type="checkbox" name="laporan_harian" value="1" role="switch" id="switch3" {{ ($settings['laporan_harian'] ?? '0') == '1' ? 'checked' : '' }} style="transform: scale(1.3);">
                            </div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">Simpan Preferensi</button>
                        </div>
                    </form>
                </div>

                <!-- Tab 3: Backup Database -->
                <div class="tab-pane fade" id="v-pills-backup" role="tabpanel">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fa-solid fa-database text-success me-2"></i>Manajemen Pencadangan (Backup)</h5>
                    
                    <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center mb-4">
                        <i class="fa-solid fa-shield-halved fs-3 text-info me-3"></i>
                        <div>
                            <strong>Penting:</strong> Lakukan pencadangan (Backup) database secara berkala untuk menghindari kehilangan data akibat kegagalan server atau kesalahan sistem.
                        </div>
                    </div>

                    <div class="card border border-light shadow-sm mb-4">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="fw-bold mb-1">Backup Keseluruhan Database</h6>
                                <p class="small text-muted mb-0">Format output: .SQLITE. Berisi seluruh data Aset, Persediaan, Opname, dan Riwayat Transaksi.</p>
                                <small class="text-success fw-bold"><i class="fa-solid fa-check me-1"></i>Database aktif: SQLite KSOP Banten</small>
                            </div>
                            <a href="{{ route('settings.backup') }}" class="btn btn-success fw-bold px-4 shadow-sm">
                                <i class="fa-solid fa-cloud-arrow-down me-2"></i> Download Backup
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .nav-pills .nav-link { color: #6c757d; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; box-shadow: 0 4px 6px rgba(13,110,253,.2); }
    .w-20px { width: 25px; }
</style>
@endsection