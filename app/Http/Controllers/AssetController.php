<?php

namespace App\Http\Controllers;

use App\Models\Aset;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Ruangan;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    /**
     * Menampilkan daftar seluruh aset
     */
    public function index()
    {
        $assets = Aset::with(['jenisBarang.kategori', 'ruangan'])->latest()->get();
        return view('assets.index', compact('assets'));
    }

    /**
     * Membuka form pendaftaran aset baru
     */
    public function create()
    {
        $categories = Kategori::all();
        $subCategories = collect([
            (object)['id' => 'Peralatan Kantor', 'name' => 'Peralatan Kantor'],
            (object)['id' => 'Elektronik & IT', 'name' => 'Elektronik & IT'],
            (object)['id' => 'Kendaraan Dinas', 'name' => 'Kendaraan Dinas'],
            (object)['id' => 'Meubelair', 'name' => 'Meubelair'],
        ]);
        $rooms = Ruangan::all();

        return view('assets.create', compact('categories', 'subCategories', 'rooms'));
    }

    /**
     * Menyimpan aset baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|unique:asets,kode_aset',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:kategoris,id',
            'room_id' => 'required|exists:ruangans,id',
            'condition' => 'required',
            'acquisition_value' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'kode_bmn' => 'nullable|string|max:30',
            'nup' => 'nullable|integer|min:1',
            'no_seri' => 'nullable|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:150',
        ]);

        $kondisi = match (strtolower(str_replace(' ', '_', $request->condition))) {
            'rusak_ringan' => 'rusak_ringan',
            'rusak_berat' => 'rusak_berat',
            default => 'baik',
        };

        // Temukan atau buat JenisBarang
        $jenisBarang = JenisBarang::firstOrCreate(
            ['nama_generik' => $request->name, 'kategori_id' => $request->category_id]
        );

        $subKategori = $request->sub_category_name ?: ($request->sub_category_id ?: 'Peralatan Kantor');

        $aset = Aset::create([
            'kode_aset' => $request->asset_code,
            'kode_bmn' => $request->kode_bmn,
            'nup' => $request->nup ?: 1,
            'jenis_barang_id' => $jenisBarang->id,
            'sub_kategori' => $subKategori,
            'merk' => $request->name,
            'model' => $request->model ?? '',
            'no_seri' => $request->no_seri,
            'kondisi' => $kondisi,
            'ruangan_id' => $request->room_id,
            'penanggung_jawab' => $request->penanggung_jawab,
            'nilai_perolehan' => $request->acquisition_value,
            'tanggal_perolehan' => $request->tanggal_perolehan ?? now()->toDateString(),
            'masa_manfaat' => $request->useful_life_years,
            'metode_penyusutan' => 'Garis Lurus',
            'akumulasi_penyusutan' => 0,
            'nilai_buku' => $request->acquisition_value,
            'terakhir_dihitung_semester' => date('Y') . '-' . (date('n') <= 6 ? '1' : '2'),
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul' => 'Aset',
            'aksi' => 'Tambah',
            'detail' => 'Mendaftarkan aset baru: ' . $aset->kode_aset . ' (' . $aset->merk . ') - NUP: ' . ($aset->nup ?? 1),
        ]);

        return redirect()->route('assets.index')->with('success', 'Aset baru (' . $aset->kode_aset . ') berhasil didaftarkan!');
    }

    /**
     * Menampilkan detail informasi dan riwayat aset
     */
    public function show($id)
    {
        $asset = Aset::with(['jenisBarang.kategori', 'ruangan', 'riwayat'])->findOrFail($id);
        return view('assets.show', compact('asset'));
    }

    /**
     * Menampilkan form edit data aset
     */
    public function edit($id)
    {
        $asset = Aset::with(['jenisBarang.kategori', 'ruangan'])->findOrFail($id);
        $categories = Kategori::all();
        $subCategories = collect([
            (object)['id' => 'Peralatan Kantor', 'name' => 'Peralatan Kantor'],
            (object)['id' => 'Elektronik & IT', 'name' => 'Elektronik & IT'],
            (object)['id' => 'Kendaraan Dinas', 'name' => 'Kendaraan Dinas'],
            (object)['id' => 'Meubelair', 'name' => 'Meubelair'],
        ]);
        $rooms = Ruangan::all();

        return view('assets.edit', compact('asset', 'categories', 'subCategories', 'rooms'));
    }

    /**
     * Memproses pembaruan data aset
     */
    public function update(Request $request, $id)
    {
        $aset = Aset::findOrFail($id);

        $request->validate([
            'asset_code' => 'required|unique:asets,kode_aset,' . $aset->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:kategoris,id',
            'room_id' => 'required|exists:ruangans,id',
            'condition' => 'required',
            'acquisition_value' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'kode_bmn' => 'nullable|string|max:30',
            'nup' => 'nullable|integer|min:1',
            'no_seri' => 'nullable|string|max:100',
            'penanggung_jawab' => 'nullable|string|max:150',
        ]);

        $kondisi = match (strtolower(str_replace(' ', '_', $request->condition))) {
            'rusak_ringan' => 'rusak_ringan',
            'rusak_berat' => 'rusak_berat',
            default => 'baik',
        };

        // Update jenis barang
        if ($aset->jenisBarang) {
            $aset->jenisBarang->update([
                'nama_generik' => $request->name,
                'kategori_id' => $request->category_id,
            ]);
        }

        $aset->update([
            'kode_aset' => $request->asset_code,
            'kode_bmn' => $request->kode_bmn,
            'nup' => $request->nup ?: 1,
            'sub_kategori' => $request->sub_category_id ?? $aset->sub_kategori,
            'merk' => $request->name,
            'no_seri' => $request->no_seri,
            'kondisi' => $kondisi,
            'ruangan_id' => $request->room_id,
            'penanggung_jawab' => $request->penanggung_jawab,
            'nilai_perolehan' => $request->acquisition_value,
            'masa_manfaat' => $request->useful_life_years,
        ]);

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul' => 'Aset',
            'aksi' => 'Edit',
            'detail' => 'Memperbarui data aset: ' . $aset->kode_aset . ' (' . $aset->merk . ')',
        ]);

        return redirect()->route('assets.index')->with('success', 'Data aset (' . $aset->kode_aset . ') berhasil diperbarui!');
    }

    /**
     * Menghapus data aset
     */
    public function destroy($id)
    {
        $aset = Aset::findOrFail($id);
        $kode = $aset->kode_aset;
        $merk = $aset->merk;

        $aset->delete();

        AuditLog::create([
            'user_id' => Auth::id() ?? 1,
            'user_name' => Auth::user()->name ?? 'Administrator',
            'modul' => 'Aset',
            'aksi' => 'Hapus',
            'detail' => 'Menghapus aset: ' . $kode . ' (' . $merk . ')',
        ]);

        return redirect()->route('assets.index')->with('success', 'Aset (' . $kode . ') berhasil dihapus!');
    }
}