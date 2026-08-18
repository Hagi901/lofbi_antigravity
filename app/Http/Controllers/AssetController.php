<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Room;

class AssetController extends Controller
{
    /**
     * 1. Menampilkan daftar aset (yang sudah kita buat sebelumnya)
     */
    public function index()
    {
        // Ambil data aset dari database (sesuaikan dengan query milikmu)
        $assets = \App\Models\Asset::with(['category', 'room'])->get(); 

        // UBAH BARIS INI: Gunakan 'assets.index' bukan 'assets'
        return view('assets.index', compact('assets')); 
    }

    /**
     * 2. Menyiapkan data dan membuka halaman Form Tambah Aset
     */
    public function create()
    {
        // Ambil data dari database untuk mengisi dropdown (pilihan) di form
        $categories = Category::all();
        $subCategories = SubCategory::all();
        $rooms = Room::all();

        return view('assets.create', compact('categories', 'subCategories', 'rooms'));
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $asset = \App\Models\Asset::findOrFail($id);
        $categories = \App\Models\Category::all(); // Sesuaikan modelmu
        $subCategories = \App\Models\SubCategory::all(); // Sesuaikan modelmu
        $rooms = \App\Models\Room::all(); // Sesuaikan modelmu

        return view('assets.edit', compact('asset', 'categories', 'subCategories', 'rooms'));
    }

    // Memproses pembaruan data
    public function update(Request $request, $id)
    {
        // Lakukan validasi dan simpan perubahan...
        
        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui!');
    }

    /**
     * 3. Menerima data dari Form dan menyimpannya ke Database
     */
    public function store(Request $request)
    {
        // Validasi input: pastikan data yang dimasukkan benar dan kode aset tidak dobel
        $validatedData = $request->validate([
            'asset_code' => 'required|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'room_id' => 'required|exists:rooms,id',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'acquisition_value' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
        ]);

        // Nilai Buku (book_value) otomatis disamakan dengan Nilai Perolehan saat pertama kali dibeli
        $validatedData['book_value'] = $validatedData['acquisition_value'];

        // Simpan ke database
        Asset::create($validatedData);

        // Kembalikan ke halaman daftar aset dengan pesan sukses
        return redirect()->route('assets.index')->with('success', 'Aset baru berhasil ditambahkan!');
    }
}