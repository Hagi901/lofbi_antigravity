<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * 1. Menampilkan Halaman Form Login
     */
    public function showLoginForm()
    {
        // Sekarang langsung memanggil file login.blade.php di dalam folder views
        return view('login');
    }

    /**
     * 2. Memproses Data Login (Mengecek Email & Password)
     */
    public function login(Request $request)
    {
        // Validasi inputan wajib diisi
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Cek kecocokan di database
        if (Auth::attempt($credentials)) {
            // Jika cocok, buatkan sesi baru agar aman dari serangan
            $request->session()->regenerate(); 
            
            // Arahkan ke dashboard
            return redirect()->intended('dashboard')->with('success', 'Selamat datang kembali di Sistem LOFBI!');
        }

        // Jika gagal (salah password/email), kembalikan ke halaman login
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * 3. Memproses Keluar (Logout)
     */
    public function logout(Request $request)
    {
        // Hapus status login
        Auth::logout();
        
        // Hancurkan tiket sesi saat ini
        $request->session()->invalidate();
        
        // Buat token keamanan baru untuk form selanjutnya
        $request->session()->regenerateToken();

        // Arahkan kembali ke halaman login dengan pesan sukses
        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}