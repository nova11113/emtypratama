@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-primary">📦 Manajemen Stok Bahan Baku</h4>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahBahan">
            <i class="fas fa-plus-circle me-1"></i> Tambah Bahan Baru
        </button>
    </div>

    {{-- FITUR SEARCH (BARU) --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" id="inventorySearch" class="form-control border-start-0 ps-0" placeholder="Cari nama bahan atau kategori...">
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card shadow border-0" style="border-radius: 15px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="inventoryTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Nama Bahan</th>
                            <th class="text-center">Sisa Stok</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $m)
                        <tr>
                            <td class="fw-bold ps-3">
                                <span class="material-name">{{ $m->nama_bahan }}</span><br>
                                <small class="text-muted fw-normal material-category">{{ $m->kategori ?? 'Umum' }}</small>
                            </td>
                            <td class="text-center fw-bold">{{ number_format($m->stok, 2) }}</td>
                            <td class="text-center text-muted small">{{ $m->satuan }}</td>
                            <td class="text-center">
                                @if($m->stok <= $m->minimal_stok)
                                    <span class="badge bg-danger">⚠️ Stok Kritis</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">✅ Aman</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalKurangi{{ $m->id }}">
                                        <i class="fas fa-minus-circle"></i> Gunakan
                                    </button>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalUpdate{{ $m->id }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="{{ route('inventory.history', $m->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL KURANGI & MODAL UPDATE TETAP DI SINI SEPERTI KODE LU SEBELUMNYA --}}
                        
                        @empty
                        <tr><td colspan="5" class="text-center py-5">Belum ada data bahan baku.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH BAHAN BARU --}}

{{-- SCRIPT SEARCH (BARU) --}}
<script>
    document.getElementById('inventorySearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#inventoryTable tbody tr');

        rows.forEach(row => {
            // Kita ambil teks dari kolom nama bahan dan kategori
            let name = row.querySelector('.material-name').textContent.toLowerCase();
            let category = row.querySelector('.material-category').textContent.toLowerCase();
            
            if (name.includes(filter) || category.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
</script>
@endsection