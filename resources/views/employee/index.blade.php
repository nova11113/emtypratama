@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">👷 Data Karyawan Garment</h2>
        <a href="{{ route('employee.create') }}" class="btn btn-primary shadow-sm fw-bold">
            <i class="fas fa-plus-circle me-1"></i> Tambah Karyawan Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 text-start ps-4">NAMA KARYAWAN</th>
                            <th>EMAIL / USERNAME</th>
                            <th>DIVISI</th>
                            <th>NOMOR HP</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $e) {{-- Variabel perulangan adalah $e --}}
                        <tr>
                            <td class="text-start ps-4">
                                <div class="fw-bold text-dark">{{ $e->name }}</div>
                            </td>
                            <td>
                                <small class="text-muted">{{ $e->email }}</small>
                            </td>
                            <td>
                                @php
                                    // Logika warna badge berdasarkan role $e
                                    $badgeColor = [
                                        'admin' => 'bg-danger',
                                        'cutting' => 'bg-primary',
                                        'sewing' => 'bg-success',
                                        'finishing' => 'bg-warning text-dark',
                                        'qc' => 'bg-info text-dark'
                                    ][$e->role] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeColor }} px-3">
                                    {{ strtoupper($e->role) }}
                                </span>
                            </td>
                            <td>
                                @if($e->no_hp)
                                    <a href="https://wa.me/{{ $e->no_hp }}" target="_blank" class="text-decoration-none text-success fw-bold">
                                        +{{ $e->no_hp }} <i class="fab fa-whatsapp"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    {{-- Menggunakan $e->id agar sinkron --}}
                                    <a href="{{ route('employee.edit', $e->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Data">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('employee.delete', $e->id) }}" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Yakin mau hapus akun {{ $e->name }}?')">
                                        <i class="fas fa-trash"></i> Hapus
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
