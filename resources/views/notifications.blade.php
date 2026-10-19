@extends('layouts.app')

@section('page_title', 'Pusat Notifikasi & Peringatan')

@section('content')
<div class="row">
    <!-- Kolom Kiri: Peringatan Kritis -->
    <div class="col-lg-8 mb-4">
        
        {{-- 0. Notifikasi Sistem Validasi Upload --}}
        @if(isset($notifikasiSistem) && $notifikasiSistem->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="fa-solid fa-bell text-primary me-2"></i>Pemberitahuan Sistem & Validasi
                    </h6>
                    <form action="{{ route('notifications.markAllRead') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 11px;">
                            <i class="fa-solid fa-check-double me-1"></i> Tandai Semua Dibaca
                        </button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($notifikasiSistem as $notif)
                            <div class="list-group-item p-3 border-bottom {{ is_null($notif->dibaca_pada) ? 'bg-white' : 'bg-light text-muted' }}">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;
                                        {{ $notif->jenis === 'upload_baru' ? 'background-color: #EBF5FF; color: #1E40AF;' : ($notif->jenis === 'ada_ditolak' ? 'background-color: #FEE2E2; color: #DC2626;' : 'background-color: #ECFDF5; color: #059669;') }}">
                                        @if($notif->jenis === 'upload_baru')
                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                        @elseif($notif->jenis === 'ada_ditolak')
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        @else
                                            <i class="fa-solid fa-circle-check"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <h6 class="mb-0 fw-bold {{ is_null($notif->dibaca_pada) ? 'text-dark' : 'text-secondary' }}" style="font-size: 13.5px;">
                                                {{ $notif->judul }}
                                            </h6>
                                            <span class="text-muted text-nowrap" style="font-size: 11px;">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="mb-2 small {{ is_null($notif->dibaca_pada) ? 'text-secondary' : 'text-muted' }}" style="font-size: 12.5px;">
                                            {{ $notif->pesan }}
                                        </p>
                                        @if($notif->tautan)
                                            <a href="{{ $notif->tautan }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-none" style="font-size: 11px;">
                                                @if($notif->jenis === 'upload_baru')
                                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Periksa Berkas
                                                @elseif($notif->jenis === 'ada_ditolak')
                                                    <i class="fa-solid fa-filter me-1"></i> Lihat yang Ditolak
                                                @elseif($notif->jenis === 'disetujui')
                                                    <i class="fa-solid fa-boxes-stacked me-1"></i> Lihat Data
                                                @else
                                                    <i class="fa-solid fa-arrow-right me-1"></i> Buka
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 1. Pengajuan Menunggu --}}
        @if($pengajuanMenunggu->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-warning border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-clock text-warning me-2"></i>Pengajuan Barang Keluar Menunggu Validasi
                        </h6>
                        @if(in_array(Auth::user()->role ?? '', ['admin', 'validator']))
                            <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm fw-bold px-3">
                                Buka Validasi <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        @endif
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($pengajuanMenunggu as $p)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark">{{ $p->persediaan?->name }}</strong>
                                    <span class="text-muted small ms-2">({{ $p->jumlah }} Unit) - untuk {{ $p->unit_kerja_penerima }}</span>
                                </div>
                                <small class="text-muted">{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('d M Y') : '-' }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. Peringatan Stok Menipis --}}
        @if($stokMenipis->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-danger border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Peringatan Stok Menipis / Habis
                        </h6>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-danger btn-sm fw-bold px-3">
                            Lihat Stok <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small fw-bold">NAMA BARANG</th>
                                    <th class="small fw-bold text-center">SISA STOK</th>
                                    <th class="small fw-bold text-center">BATAS MINIMUM</th>
                                    <th class="small fw-bold text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stokMenipis as $item)
                                    @php $sisa = $item->batches->sum('sisa_stok'); @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $item->name }}</td>
                                        <td class="text-center fw-bold text-danger">{{ $sisa }} {{ $item->satuan }}</td>
                                        <td class="text-center text-muted">{{ $item->stok_minimum }} {{ $item->satuan }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $sisa <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                {{ $sisa <= 0 ? 'Habis' : 'Menipis' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Aset Habis Umur Ekonomis --}}
        @if($asetHabisUmur->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-secondary border-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fa-solid fa-hourglass-end text-secondary me-2"></i>Aset Telah Habis Umur Ekonomis (Nilai Buku Rp 0)
                        </h6>
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm fw-bold px-3">
                            Kelola Aset <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($asetHabisUmur as $a)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-dark">{{ $a->kode_aset }}</strong> - {{ $a->name }}
                                    <span class="text-muted small ms-2">({{ $a->ruangan?->nama ?? '-' }})</span>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary border">100% Terdepresiasi</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($pengajuanMenunggu->count() == 0 && $stokMenipis->count() == 0 && $asetHabisUmur->count() == 0)
            <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                <div class="card-body">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-circle-check fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Semua Sistem Dalam Kondisi Normal</h5>
                    <p class="text-muted small mb-0">Tidak ada peringatan kritis, stok menipis, ataupun pengajuan yang tertunda.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Kolom Kanan: Log Aktivitas Terbaru -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom-0">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Log Aktivitas Terbaru
                </h6>
            </div>
            <div class="card-body p-4 pt-0">
                <div class="timeline">
                    @forelse($recentLogs as $log)
                        <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                            <div class="position-absolute bg-primary rounded-circle" style="width: 10px; height: 10px; left: -6px; top: 5px;"></div>
                            <p class="fw-bold text-dark mb-0 small">{{ $log->modul }} &bull; {{ $log->aksi }}</p>
                            <p class="text-muted small mb-0" style="font-size: 11px;">{{ $log->detail }}</p>
                            <small class="text-secondary" style="font-size: 10px;">
                                {{ $log->user_name ?? 'Sistem' }} &bull; {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted small text-center py-4">Belum ada riwayat aktivitas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
