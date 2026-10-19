# Fixture untuk Pengujian

Folder ini menyimpan berkas fixture kecil yang digunakan oleh test suite. Fixture dibuat seminimal mungkin agar tes berjalan cepat.

## Fixture yang Tersedia

| Nama Berkas | Digunakan Oleh | Deskripsi |
|---|---|---|
| `sakti_opname_dummy.csv` | Feature Tests | Format CSV dummy opname fisik persediaan SAKTI (kode barang 16 digit, baris placeholder nama/satuan strip). |

## Cara Menambah Fixture

1. Simpan berkas di folder ini.
2. Referensikan dari test dengan: `$file = new \Illuminate\Http\UploadedFile(base_path('tests/fixtures/sakti_opname_dummy.csv'), 'sakti_opname_dummy.csv', null, null, true);`
3. Dokumentasikan berkas baru di tabel di atas.
