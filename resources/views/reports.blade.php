@extends('layouts.app')

@section('page_title', 'Pusat Unduh Laporan')

@section('content')
<!-- Baris Filter Laporan -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body bg-light rounded d-flex flex-column flex-md-row justify-content-between align-items-center">
        <h6 class="mb-3 mb-md-0 fw-bold text-dark"><i class="fa-solid fa-filter me-2 text-primary"></i>Parameter Laporan</h6>
        <form class="d-flex gap-2 align-items-center">
            <select class="form-select form-select-sm border-0 shadow-sm fw-bold text-secondary">
                <option value="">Semua Kategori</option>
                <option value="atk">Alat Tulis Kantor (ATK)</option>
                <option value="elektronik">Barang Elektronik</option>
                <option value="furnitur">Furnitur Ruangan</option>
            </select>
            
            <!-- Bulan Otomatis -->
            <select class="form-select form-select-sm border-0 shadow-sm fw-bold text-secondary">
                <option value="08" {{ date('m') == '08' ? 'selected' : '' }}>Agustus</option>
                <option value="09" {{ date('m') == '09' ? 'selected' : '' }}>September</option>
                <option value="10" {{ date('m') == '10' ? 'selected' : '' }}>Oktober</option>
                <option value="11" {{ date('m') == '11' ? 'selected' : '' }}>November</option>
                <option value="12" {{ date('m') == '12' ? 'selected' : '' }}>Desember</option>
            </select>
            
            <!-- Tahun Otomatis -->
            <select class="form-select form-select-sm border-0 shadow-sm fw-bold text-secondary">
                <option value="{{ date('Y') }}" selected>{{ date('Y') }}</option>
                <option value="{{ date('Y') - 1 }}">{{ date('Y') - 1 }}</option>
            </select>
            
            <button type="button" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Kartu Laporan Aset -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover">
            <div class="bg-primary position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-desktop text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Laporan Aset Tetap</h5>
                <p class="small text-muted mb-4 px-2">Rekapitulasi total aset, kondisi barang terkini, dan sebaran lokasi ruangan di KSOP Kelas I Banten.</p>
                
                <div class="d-flex gap-2 justify-content-center px-3">
                    <button id="btn-pdf-aset" class="btn btn-outline-danger btn-sm fw-bold flex-grow-1"><i class="fa-solid fa-file-pdf me-1"></i> PDF</button>
                    <button id="btn-excel-aset" class="btn btn-outline-success btn-sm fw-bold flex-grow-1"><i class="fa-solid fa-file-excel me-1"></i> Excel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Laporan Persediaan -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover">
            <div class="bg-success position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-boxes-stacked text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Laporan Persediaan</h5>
                <p class="small text-muted mb-4 px-2">Riwayat mutasi barang masuk & keluar (Metode FIFO) beserta sisa saldo stok gudang saat ini.</p>
                
                <div class="d-flex gap-2 justify-content-center px-3">
                    <button id="btn-pdf-fifo" class="btn btn-outline-danger btn-sm fw-bold flex-grow-1"><i class="fa-solid fa-file-pdf me-1"></i> PDF</button>
                    <button id="btn-excel-fifo" class="btn btn-outline-success btn-sm fw-bold flex-grow-1"><i class="fa-solid fa-file-excel me-1"></i> Excel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Berita Acara -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden card-hover">
            <div class="bg-warning position-absolute top-0 start-0 w-100" style="height: 4px;"></div>
            <div class="card-body text-center py-5">
                <div class="mb-4 d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-file-signature text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Berita Acara Opname</h5>
                <p class="small text-muted mb-4 px-2">Cetak dokumen resmi hasil verifikasi fisik gudang untuk pelaporan pertanggungjawaban pimpinan.</p>
                
                <div class="px-3">
                    <button id="btn-cetak-ba" class="btn btn-dark w-100 btn-sm fw-bold"><i class="fa-solid fa-print me-1"></i> Cetak Berita Acara</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card-hover:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<!-- Tambahkan Pustaka Animasi SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Fungsi simulasi loading yang canggih
    function simulateDownload(fileType, moduleName) {
        Swal.fire({
            title: 'Memproses Data...',
            html: 'Sistem sedang menyusun ' + moduleName + ' (Format: <b>' + fileType + '</b>)',
            timer: 2000,
            timerProgressBar: true,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                // Tampilkan pesan sukses setelah loading selesai
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Dokumen ' + moduleName + ' siap diunduh.',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });
    }

    // Sambungkan fungsi ke masing-masing tombol
    document.getElementById('btn-pdf-aset').addEventListener('click', function() { simulateDownload('PDF', 'Laporan Aset'); });
    document.getElementById('btn-excel-aset').addEventListener('click', function() { simulateDownload('Excel', 'Laporan Aset'); });
    
    document.getElementById('btn-pdf-fifo').addEventListener('click', function() { simulateDownload('PDF', 'Laporan Persediaan'); });
    document.getElementById('btn-excel-fifo').addEventListener('click', function() { simulateDownload('Excel', 'Laporan Persediaan'); });
    
    document.getElementById('btn-cetak-ba').addEventListener('click', function() { simulateDownload('PDF', 'Berita Acara Opname'); });
</script>
@endsection