# Berkas Contoh (Sample Files)

Folder ini menyimpan berkas contoh (sample/fixture) yang digunakan sebagai referensi format input untuk modul Upload Staging & Validasi.

## Berkas yang Diharapkan

| Nama Berkas | Sumber | Status | Keterangan |
|---|---|---|---|
| `bahan_opname_fisik.xlsx` | SAKTI | **Tersedia** | Berkas opname fisik persediaan dari aplikasi SAKTI Kemenkeu. Dua sheet: "Data master" (~842 baris) dan "input LOFBI" (~76 baris saldo > 0). Tidak memuat harga satuan. |
| `contoh_siman_aset.xlsx` | SIMAN | **PENDING** | Berkas daftar inventaris aset BMN dari aplikasi SIMAN. Kolom yang diharapkan: Kode BMN, NUP, Nama Barang, Merk, Nilai Perolehan, Tanggal Perolehan, Masa Manfaat, Kondisi. |
| `contoh_rincian_buku_persediaan.pdf` | SAKTI | **PENDING** | Berkas PDF berbasis teks Rincian Buku Persediaan dari SAKTI. Parser PDF akan diimplementasikan setelah berkas sampel tersedia. |

## Catatan Teknis

- Kode barang persediaan berformat **16 digit** (contoh: `1010108047000001`), disimpan sebagai STRING.
- Format Word (`.docx`) ditolak secara tegas oleh sistem.
- Berkas SAKTI opname fisik tidak memuat harga satuan; kolom `harga_belum_diisi = true` dan `harga_satuan = NULL` akan diset otomatis.
- Jangan pernah menebak format kolom SIMAN sebelum berkas sampel resmi tersedia.
