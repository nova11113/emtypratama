<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthWebController extends Controller
{
    public function form()
    {
        // Kalau sudah login, langsung lempar ke dashboard
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'role' => ['required'] 
        ]);

        $authData = $request->only('email', 'password');

        // 2. Coba Login
        if (Auth::attempt($authData)) {
            $user = Auth::user();
            
            // 3. Normalisasi Role (Biar Gak Sensitif Huruf Gede/Kecil & Spasi)
            $roleDiDB = strtolower(trim($user->role)); 
            $roleDiForm = strtolower(trim($request->role)); 

            // 4. Cek Cocok Gak Rolenya
            if ($roleDiDB !== $roleDiForm) {
                Auth::logout();
                
                // Kasih tau role aslinya apa biar lu gak bingung
                $pesan = "Akses ditolak! Divisi '" . strtoupper($roleDiForm) . "' salah. ";
                $pesan .= "Akun ini terdaftar di divisi: " . strtoupper($user->role);
                
                return back()->with('error', $pesan);
            }

            // 5. Sukses Login
            $request->session()->regenerate();
            
            // Paksa ke dashboard (jangan pake intended biar gak nyasar ke link admin)
            return redirect('/dashboard');
        }

        // 6. Gagal Login
        return back()->with('error', 'Email atau Password salah bro!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}