<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::whereNotNull('role')->get();
        return view('employee.index', compact('employees'));
    }

    public function create()
    {
        return view('employee.create');
    }

    public function store(Request $r)
    {
        // 1. Validasi (Sesuaikan dengan name di Blade lu)
        $r->validate([
            'email'    => 'required|email|unique:users,email',
            'nama'     => 'required', // Di Blade lu name="nama"
            'password' => 'required|min:6',
            'role'     => 'required',
            'no_hp'    => 'required',
        ]);

        // 2. Simpan ke tabel users
        User::create([
            'name'     => $r->nama, // Tangkap 'nama' dari Blade, masukkan ke kolom 'name' DB
            'email'    => $r->email,
            'password' => Hash::make($r->password), 
            'role'     => strtolower($r->role),
            'no_hp'    => $r->no_hp,
        ]);

        return redirect('/karyawan')->with('success', 'Akun divisi berhasil dibuat!');
    }

    public function edit($id)
    {
        $employee = User::findOrFail($id);
        return view('employee.edit', compact('employee'));
    }

    public function update(Request $r, $id)
    {
        $user = User::findOrFail($id);
        
        $r->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'nama'  => 'required',
        ]);

        $data = [
            'name'  => $r->nama, // Samakan juga di sini
            'email' => $r->email,
            'role'  => strtolower($r->role),
            'no_hp' => $r->no_hp,
        ];

        if ($r->filled('password')) {
            $data['password'] = Hash::make($r->password);
        }

        $user->update($data);
        return redirect('/karyawan')->with('success', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Karyawan berhasil dihapus!');
    }
}