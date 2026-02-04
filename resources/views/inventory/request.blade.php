@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0 text-primary">📩 Permintaan Bahan Baku</h4>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahRequest">
            <i class="fas fa-paper-plane me-1"></i> Buat Permintaan
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm">{{ session('error') }}</div>
    @endif

    <div class="card shadow border-0" style="border-radius: 15px;">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-center">
                        <tr>
                            <th>Kode & Tanggal</th>
                            <th>Bahan</th>
                            <th>Jumlah</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        <tr>
                            <td class="small text-center">
                                <b>{{ $req->kode_request }}</b><br>
                                <span class="text-muted">{{ $req->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td>
                                <b>{{ $req->material->nama_bahan ?? 'Bahan Terhapus' }}</b><br>
                                <small class="text-muted">Oleh: {{ $req->user->name ?? 'User' }}</small>
                            </td>
                            <td class="text-center fw-bold">{{ $req->qty_minta }} {{ $req->material->satuan ?? '' }}</td>
                            <td>{{ $req->keperluan }}</td>
                            <td class="text-center">
                                @if($req->status == 'pending')
                                    <span class="badge bg-warning text-dark">⏳ Pending</span>
                                @elseif($req->status == 'disetujui')
                                    <span class="badge bg-success">✅ Disetujui</span>
                                @else
                                    <span class="badge bg-danger">❌ Ditolak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($req->status == 'pending' && in_array(strtolower(Auth::user()->role), ['admin', 'gudang']))
                                    <form action="{{ route('inventory.request.approve', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-bold" onclick="return confirm('Setujui permintaan? Stok akan otomatis terpotong.')">
                                            Approve
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5">Belum ada antrean permintaan bahan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL BUAT PERMINTAAN --}}
<div class="modal fade" id="modalTambahRequest" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Form Permintaan Bahan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.request.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Bahan</label>
                        <select name="material_id" class="form-select" required>
                            <option value="">-- Pilih Bahan Baku --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->nama_bahan }} (Stok: {{ $m->stok }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Diminta</label>
                        <input type="number" name="qty_minta" class="form-control" step="0.01" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keperluan / Untuk PO</label>
                        <textarea name="keperluan" class="form-control" rows="3" placeholder="Contoh: Produksi PO-001" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Kirim Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection