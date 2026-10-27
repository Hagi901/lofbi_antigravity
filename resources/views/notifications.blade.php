@extends('layouts.app')

@section('page_title', 'Pusat Notifikasi & Peringatan')

@section('content')
<!-- Page Header -->
<x-page-header title="Pusat Notifikasi &amp; Peringatan" subtitle="Pemberitahuan alur validasi, peringatan stok, dan catatan aktivitas sistem">
    <x-slot:actions>
        @if(isset($notifikasiSistem) && $notifikasiSistem->count() > 0)
            <form action="{{ route('notifications.markAllRead') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-light btn-sm text-primary">
                    <i class="fa-solid fa-check-double me-1"></i> Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </x-slot:actions>
</x-page-header>

<div class="row">
    <!-- Kolom Kiri: Peringatan Kritis -->
    <div class="col-lg-8 mb-4">
        {{-- 0. Notifikasi Sistem Validasi Upload --}}
        @if(isset($notifikasiSistem) && $notifikasiSistem->count() > 0)
            <div class="card-app mb-4">
                <div class="card-header-app">
                    <h2 class="fs-6 fw-bold text-dark mb-0">
                        <i class="fa-solid fa-bell text-primary me-2"></i>Pemberitahuan Sistem &amp; Validasi
                    </h2>
                </div>
                <div class="card-body-app p-0">
                    <div class="list-group list-group-flush">
                        @foreach($notifikasiSistem as $notif)
                            <div class="list-group-item p-3 border-bottom {{ is_null($notif->dibaca_pada) ? 'bg-white' : 'bg-transparent text-muted' }}">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="rounded-circle p-2 mt-1 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;
                                        {{ $notif->jenis === 'upload_baru' ? 'background-color: var(--color-primary-light); color: var(--color-primary);' : ($notif->jenis === 'ada_ditolak' ? 'background-color: var(--color-danger-light); color: var(--color-danger);' : 'background-color: var(--color-success-light); color: var(--color-success);') }}">
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
                                            <h3 class="mb-0 fw-bold {{ is_null($notif->dibaca_pada) ? 'text-dark' : 'text-secondary' }}" style="font-size: 13.5px;">
                                                {{ $notif->judul }}
                                            </h3>
                                            <span class="text-muted text-nowrap" style="font-size: 11px;">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="mb-2 small {{ is_null($notif->dibaca_pada) ? 'text-secondary' : 'text-muted' }}" style="font-size: 12.5px; line-height: 1.45; word-break: break-word;">
                                            {{ $notif->pesan }}
                                        </p>

                                        @if($notif->jenis === 'ada_ditolak')
                                            @php
                                                $notifData = $notif->data ?? [];
                                                $nomorBaris = $notifData['nomor_baris'] ?? null;
                                                $namaBarang = $notifData['nama_barang'] ?? null;
                                                $kodeBarang = $notifData['kode_barang'] ?? null;
                                                $catatan = $notifData['deskripsi_revisi'] ?? null;
                                            @endphp
                                            <div class="mb-3 p-2 px-3 rounded-2 bg-light border border-danger-subtle small" style="word-break: break-word; font-size: 12px;">
                                                @if($nomorBaris || $namaBarang || $kodeBarang)
                                                    <div class="mb-1 text-muted">
                                                        @if($nomorBaris)
                                                            <span class="badge bg-white text-dark border me-1">Baris #{{ $nomorBaris }}</span>
                                                        @endif
                                                        @if($namaBarang)
                                                            <strong class="text-dark">{{ $namaBarang }}</strong>
                                                        @endif
                                                        @if($kodeBarang)
                                                            <span class="text-muted">({{ $kodeBarang }})</span>
                                                        @endif
                                                    </div>
                                                @endif
                                                @if($catatan)
                                                    <div class="fw-semibold text-danger mb-0">Catatan penolakan:</div>
                                                    <div class="text-secondary text-wrap" style="line-height: 1.45;">{{ $catatan }}</div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($notif->tautan)
                                            <a href="{{ $notif->tautan }}" class="btn btn-sm btn-primary">
                                                @if($notif->jenis === 'upload_baru')
                                                    <i class="fa-solid fa-magnifying-glass me-1"></i> Periksa Berkas
                                                @elseif($notif->jenis === 'ada_ditolak')
                                                    <i class="fa-solid fa-wrench me-1"></i> Perbaiki
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
            <div class="card-app mb-4" style="border-left: 4px solid var(--color-warning);">
                <div class="card-body-app p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fs-6 fw-bold text-dark mb-0">
                            <i class="fa-solid fa-clock text-warning me-2"></i>Pengajuan Barang Keluar Menunggu Validasi
                        </h2>
                        @if(in_array(Auth::user()->role ?? '', ['admin', 'validator']))
                            <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm">
                                Validasi Pengajuan <i class="fa-solid fa-arrow-right ms-1"></i>
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
            <div class="card-app mb-4" style="border-left: 4px solid var(--color-danger);">
                <div class="card-body-app p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fs-6 fw-bold text-dark mb-0">
                            <i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Peringatan Stok Menipis / Habis
                        </h2>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-danger btn-sm">
                            Lihat Stok <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size: 13px;">
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
                                            <span class="badge-app {{ $sisa <= 0 ? 'badge-app-danger' : 'badge-app-warning' }}">
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
            <div class="card-app mb-4" style="border-left: 4px solid var(--color-text-muted);">
                <div class="card-body-app p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="fs-6 fw-bold text-dark mb-0">
                            <i class="fa-solid fa-hourglass-end text-secondary me-2"></i>Aset Telah Habis Umur Ekonomis (Nilai Buku Rp 1)
                        </h2>
                        <a href="{{ route('assets.index') }}" class="btn btn-light btn-sm">
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
                                <span class="badge-app badge-app-secondary">100% Terdepresiasi</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($pengajuanMenunggu->count() == 0 && $stokMenipis->count() == 0 && $asetHabisUmur->count() == 0)
            <div class="card-app text-center py-5">
                <div class="card-body-app">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: var(--color-success-light); color: var(--color-success); font-size: 2rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <h2 class="fw-bold text-dark fs-6 mb-1">Semua Sistem Dalam Kondisi Normal</h2>
                    <p class="text-muted small mb-0">Tidak ada peringatan kritis, stok menipis, ataupun pengajuan yang tertunda.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Kolom Kanan: Log Aktivitas Terbaru -->
    <div class="col-lg-4 mb-4">
        <div class="card-app h-100">
            <div class="card-header-app">
                <h2 class="fs-6 fw-bold text-dark mb-0">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Log Aktivitas Terbaru
                </h2>
            </div>
            <div class="card-body-app p-4 pt-0">
                <div class="timeline">
                    @forelse($recentLogs as $log)
                        <div class="border-start border-2 ps-3 pb-3 position-relative" style="border-color: var(--color-primary-border) !important;">
                            <div class="position-absolute rounded-circle" style="width: 10px; height: 10px; left: -6px; top: 5px; background: var(--color-primary);"></div>
                            <p class="fw-bold text-dark mb-0 small">{{ $log->modul }} &bull; {{ $log->aksi }}</p>
                            <p class="text-muted small mb-0" style="font-size: 11.5px;">{{ $log->detail }}</p>
                            <small class="text-secondary" style="font-size: 10.5px;">
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
