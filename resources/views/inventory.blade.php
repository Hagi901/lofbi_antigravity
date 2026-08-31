@extends('layouts.app')

@section('page_title', 'Persediaan Barang (Metode FIFO)')

@section('content')
<!-- Ringkasan Cepat Persediaan -->
<div class="row mb-4">
    <!-- Kartu 1: Total Jenis Barang -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-success border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-boxes-packing text-success fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Jenis Barang</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalItems ?? $items->count()) }} <span class="fs-6 text-muted fw-normal">Item</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 2: Total Seluruh Stok Fisik -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-primary border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-box-open text-primary fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Seluruh Stok Fisik</p>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalStok ?? 0) }} <span class="fs-6 text-muted fw-normal">Unit</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu 3: Total Nilai Rupiah Saldo Gudang -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-warning border-4">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-rupiah-sign text-warning fs-4"></i>
                </div>
                <div>
                    <p class="text-muted small fw-bold mb-1">Total Nilai Persediaan</p>
                    <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem;">Rp {{ number_format($totalNilaiRupiah ?? 0, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Kartu Persediaan Utama -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom-0 gap-2">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-check text-primary me-2"></i>Daftar Buku Persediaan Barang (FIFO)
        </h6>
        <div class="d-flex flex-wrap gap-2">
            @if(in_array(Auth::user()->role ?? '', ['validator', 'admin']) && ($pengajuanMenunggu ?? 0) > 0)
                <a href="{{ route('inventory.pengajuan') }}" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm">
                    <i class="fa-solid fa-stamp me-1"></i> Validasi Pengajuan ({{ $pengajuanMenunggu }})
                </a>
            @endif
            @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm fw-bold px-3 shadow-sm rounded-pill">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Master Barang
                </a>
                <a href="{{ route('inventory.in.create') }}" class="btn btn-success btn-sm fw-bold px-3 shadow-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Input Masuk
                </a>
                <a href="{{ route('inventory.out.create') }}" class="btn btn-danger btn-sm fw-bold px-3 shadow-sm">
                    <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Ajukan Keluar
                </a>
            @endif
        </div>
    </div>

    <div class="card-body p-4 pt-0">
        
        <!-- Notifikasi Sukses / Error -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive mt-2">
            <table class="table table-hover align-middle border-bottom">
                <thead class="table-light">
                    <tr>
                        <th class="text-secondary small fw-bold border-0" width="12%">KODE / ID</th>
                        <th class="text-secondary small fw-bold border-0" width="30%">NAMA BARANG</th>
                        <th class="text-secondary small fw-bold border-0" width="18%">KATEGORI</th>
                        <th class="text-secondary small fw-bold border-0 text-center" width="12%">SISA STOK (FIFO)</th>
                        <th class="text-secondary small fw-bold border-0 text-end" width="14%">NILAI SALDO (Rp)</th>
                        <th class="text-secondary small fw-bold border-0 text-center" width="8%">STATUS</th>
                        <th class="text-secondary small fw-bold border-0 text-center" width="10%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $sisaStok = $item->batches->sum('sisa_stok');
                            $nilaiSaldo = $item->batches->sum(fn($b) => $b->sisa_stok * $b->harga_satuan);
                            $minStok = $item->stok_minimum ?? 0;
                            
                            // Logika Status
                            if ($sisaStok > $minStok) {
                                $badge = 'bg-success-subtle text-success border-success';
                                $icon = 'fa-check';
                                $status = 'Aman';
                            } elseif ($sisaStok > 0) {
                                $badge = 'bg-warning-subtle text-warning border-warning';
                                $icon = 'fa-triangle-exclamation';
                                $status = 'Menipis';
                            } else {
                                $badge = 'bg-danger-subtle text-danger border-danger';
                                $icon = 'fa-xmark';
                                $status = 'Habis';
                            }
                        @endphp
                        <tr>
                            <td>
                                <span class="badge bg-white text-dark border shadow-sm px-2 py-1">
                                    INV-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('inventory.show', $item->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $item->name }}
                                </a>
                                @if($item->merk)
                                    <br><small class="text-muted">{{ $item->merk }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">
                                    {{ $item->jenisBarang?->kategori?->nama ?? 'Persediaan' }}
                                </span>
                            </td>
                            <td class="text-center fw-bold {{ $sisaStok <= 0 ? 'text-danger' : '' }}">
                                {{ number_format($sisaStok) }} <span class="fw-normal small text-muted">{{ $item->satuan }}</span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($nilaiSaldo, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badge }} border px-2 py-1">
                                    <i class="fa-solid {{ $icon }} me-1"></i>{{ $status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    {{-- Tombol Detail Kartu --}}
                                    <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-light btn-sm text-primary shadow-sm" title="Buku Kartu Persediaan">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </a>
                                    
                                    @if(in_array(Auth::user()->role ?? '', ['admin', 'operator']))
                                        {{-- Tombol Edit Master --}}
                                        <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-light btn-sm text-secondary shadow-sm" title="Edit Master">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        {{-- Tombol Hapus (Admin saja) --}}
                                        @if((Auth::user()->role ?? '') === 'admin')
                                            <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus master barang ini beserta seluruh riwayat batch-nya?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-light btn-sm text-danger shadow-sm" title="Hapus Master Barang">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 mb-3 d-block opacity-25"></i>
                                Belum ada data barang persediaan. Silakan klik <strong>Tambah Master Barang</strong> di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection