@extends('layouts.app') {{-- Pastikan mengarah ke layout sidebar lu --}}

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">👷 Data Karyawan Garment</h2>
        <a href="/karyawan/create" class="btn btn-primary shadow-sm fw-bold">
            + Tambah Karyawan Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3">NAMA KARYAWAN</th>
                            <th>EMAIL / USERNAME</th>
                            <th>DIVISI</th>
                            <th>NOMOR HP</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $e)
                        <tr>
                            <td class="text-start ps-4">
                                <div class="fw-bold text-dark">{{ $e->name }}</div> {{-- Ganti ke $e->name --}}
                            </td>
                            <td>
                                <small class="text-muted">{{ $e->email }}</small>
                            </td>
                            <td>
                                {{-- Warna badge otomatis sesuai divisi --}}
                                <span class="badge {{ $e->role == 'admin' ? 'bg-danger' : ($e->role == 'sewing' ? 'bg-success' : 'bg-dark') }} px-3">
                                    {{ strtoupper($e->role) }}
                                </span>
                            </td>
                            <td>
                                @if($e->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $e->no_hp) }}" target="_blank" class="text-decoration-none text-success fw-bold">
                                        {{ $e->no_hp }} 🟢
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/karyawan/{{ $e->id }}/edit" class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <a href="/karyawan/{{ $e->id }}/delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin mau hapus akun {{ $e->name }}?')">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection