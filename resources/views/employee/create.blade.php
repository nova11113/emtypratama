@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 text-center">
                    <h3 class="fw-bold text-dark">➕ Tambah Karyawan & Akun</h3>
                    <p class="text-muted small">Karyawan akan login menggunakan email & password di bawah</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="POST" action="{{ url('/karyawan/store') }}">
    @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input name="nama" type="text" class="form-control" placeholder="Nama sesuai KTP" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Login</label>
                                <input name="email" type="email" class="form-control" placeholder="contoh@gmail.com" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <input name="password" type="password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Divisi (Role Login)</label>
                            <select name="role" class="form-select" required>
                                <option value="">-- Pilih Divisi --</option>
                                <option value="admin">Admin / Kantor</option>
                                <option value="cutting">Divisi Cutting (Potong)</option>
                                <option value="sewing">Divisi Sewing (Jahit)</option>
                                <option value="finishing">Divisi Finishing</option>
                                <option value="qc">Divisi Quality Control</option>
                            </select>
                            <small class="text-muted">*Role ini menentukan menu apa yang muncul di HP mereka.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nomor HP (WhatsApp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">62</span>
                                <input name="no_hp" type="number" class="form-control" placeholder="8123456xxx" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                                Daftarkan Karyawan & Akun
                            </button>
                            <a href="/karyawan" class="btn btn-light fw-bold py-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
