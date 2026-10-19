<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\UploadBaris;
use App\Models\UploadPaket;
use App\Services\Upload\UploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService $uploadService
    ) {}

    /**
     * Daftar paket upload (untuk Admin, Operator, Validator, Pimpinan).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = UploadPaket::with(['pengunggah', 'baris'])->latest();

        // Filter berdasarkan status paket jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Operator hanya melihat paket yang diunggahnya sendiri jika bukan admin/validator/pimpinan
        if ($user->role === 'operator') {
            $query->where('diunggah_oleh', $user->id);
        }

        $pakets = $query->paginate(15);
        $ruangans = Ruangan::orderBy('nama')->get();

        // Statistik ringkas
        $statMenunggu = UploadPaket::where('status', 'menunggu')->count();
        $statSebagian = UploadPaket::where('status', 'disetujui_sebagian')->count();
        $statDisetujui = UploadPaket::where('status', 'disetujui')->count();
        $statDitolak = UploadPaket::where('status', 'ditolak')->count();

        return view('upload.index', compact('pakets', 'ruangans', 'statMenunggu', 'statSebagian', 'statDisetujui', 'statDitolak'));
    }

    /**
     * Upload berkas baru ke tabel staging.
     */
    public function store(Request $request)
    {
        $file = $request->file('file');
        if (! $file) {
            return back()->with('error', 'Berkas upload wajib dipilih.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $rejected = config('upload_mapping.rejected_extensions', []);
        if (isset($rejected[$ext])) {
            return back()->with('error', $rejected[$ext]);
        }

        $request->validate([
            'ruangan_id' => 'nullable|exists:ruangans,id',
            'sheet_name' => 'nullable|string',
        ]);

        try {
            $paket = $this->uploadService->uploadFile(
                $file,
                Auth::user(),
                $request->ruangan_id,
                $request->sheet_name
            );

            return redirect()->route('upload.show', $paket->id)
                ->with('success', 'Berkas berhasil diunggah! Sebanyak '.$paket->jumlah_baris.' baris masuk ke antrean validasi.');
        } catch (ValidationException $ve) {
            $firstError = collect($ve->errors())->flatten()->first();

            return back()->with('error', $firstError ?: 'Validasi berkas gagal.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses berkas: '.$e->getMessage());
        }
    }

    /**
     * Tampilkan detail baris-baris dalam paket upload (staging).
     */
    public function show($id, Request $request)
    {
        $paket = UploadPaket::with(['pengunggah'])->findOrFail($id);

        $barisQuery = $paket->baris()->latest('id');

        // Filter status baris: all, ditolak, menunggu, disetujui
        $filter = $request->query('filter');
        if ($filter && in_array($filter, ['menunggu', 'disetujui', 'ditolak'])) {
            $barisQuery->where('status', $filter);
        }

        $barisList = $barisQuery->paginate(25)->withQueryString();

        $countTotal = $paket->baris()->count();
        $countMenunggu = $paket->baris()->where('status', 'menunggu')->count();
        $countDisetujui = $paket->baris()->where('status', 'disetujui')->count();
        $countDitolak = $paket->baris()->where('status', 'ditolak')->count();

        return view('upload.show', compact('paket', 'barisList', 'filter', 'countTotal', 'countMenunggu', 'countDisetujui', 'countDitolak'));
    }

    /**
     * Upload ulang file revisi untuk baris-baris yang ditolak.
     */
    public function revisi($id, Request $request)
    {
        $paket = UploadPaket::findOrFail($id);

        $user = Auth::user();
        if ($user->role === 'operator' && $paket->diunggah_oleh !== $user->id) {
            abort(403, 'Anda tidak berhak merevisi paket milik pengguna lain.');
        }

        $file = $request->file('file');
        if (! $file) {
            return back()->with('error', 'Berkas revisi wajib dipilih.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $rejected = config('upload_mapping.rejected_extensions', []);
        if (isset($rejected[$ext])) {
            return back()->with('error', $rejected[$ext]);
        }

        try {
            $this->uploadService->revisiPaket($paket, $file, $user, $request->sheet_name);

            return redirect()->route('upload.show', $paket->id)
                ->with('success', 'Berkas revisi berhasil diunggah. Baris yang ditolak telah diperbarui dan menunggu validasi ulang.');
        } catch (ValidationException $ve) {
            $firstError = collect($ve->errors())->flatten()->first();

            return back()->with('error', $firstError ?: 'Validasi berkas revisi gagal.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses revisi: '.$e->getMessage());
        }
    }

    /**
     * Setujui satu baris staging (Validator).
     */
    public function setujuiBaris($barisId, Request $request)
    {
        if (Auth::user()?->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menyetujui baris unggahan.');
        }

        $baris = UploadBaris::findOrFail($barisId);
        $this->uploadService->setujuiBaris($baris, Auth::user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Baris berhasil disetujui dan dipromosikan ke inventaris.',
                'status' => $baris->fresh()->status,
            ]);
        }

        return back()->with('success', 'Baris ke-'.$baris->nomor_baris.' ('.($baris->nama_barang ?: 'Item').') berhasil disetujui.');
    }

    /**
     * Tolak satu baris staging (Validator). Wajib mencantumkan deskripsi revisi.
     */
    public function tolakBaris($barisId, Request $request)
    {
        if (Auth::user()?->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menolak baris unggahan.');
        }

        $request->validate([
            'deskripsi_revisi' => 'required|string|min:3|max:1000',
        ], [
            'deskripsi_revisi.required' => 'Deskripsi alasan penolakan/revisi wajib diisi.',
            'deskripsi_revisi.min' => 'Deskripsi revisi minimal 3 karakter.',
        ]);

        $baris = UploadBaris::findOrFail($barisId);
        $this->uploadService->tolakBaris($baris, Auth::user(), $request->deskripsi_revisi);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Baris ditolak dengan catatan revisi.',
                'status' => $baris->fresh()->status,
            ]);
        }

        return back()->with('success', 'Baris ke-'.$baris->nomor_baris.' telah ditolak.');
    }

    /**
     * Setujui semua baris yang belum ditolak dalam satu aksi (Validator).
     */
    public function setujuiSemua($id, Request $request)
    {
        if (Auth::user()?->role !== 'validator') {
            abort(403, 'Hanya role validator yang diizinkan menyetujui semua baris unggahan.');
        }

        $paket = UploadPaket::findOrFail($id);
        $count = $this->uploadService->setujuiSemua($paket, Auth::user());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Sebanyak {$count} baris berhasil disetujui.",
                'count' => $count,
            ]);
        }

        return back()->with('success', "Sebanyak {$count} baris berhasil disetujui dan dipromosikan ke inventaris resmi.");
    }
}
