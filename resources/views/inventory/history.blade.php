@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">📜 Riwayat Mutasi Bahan</h4>
            <p class="text-muted">Material: <span class="text-primary fw-bold">{{ $material->nama_bahan ?? 'Semua Bahan' }}</span></p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Kembali ke Gudang
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-secondary small text-uppercase">
                            <th class="ps-3">Waktu</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Jumlah</th>
                            <th>Keterangan</th>
                            <th class="pe-3">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- PAKAI @forelse BIAR GAK ERROR UNDEFINED --}}
                        @forelse($logs as $l)
                        <tr>
                            <td class="ps-3 small text-muted">
                                {{ $l->created_at->format('d/m/y H:i') }}
                            </td>
                            <td class="text-center">
                                @if($l->tipe == 'masuk')
                                    <span class="badge bg-success-subtle text-success px-3">MASUK</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3">KELUAR</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold {{ $l->tipe == 'masuk' ? 'text-success' : 'text-danger' }}">
                                {{ $l->tipe == 'masuk' ? '+' : '-' }}{{ number_format($l->jumlah, 2) }}
                            </td>
                            <td><span class="small text-muted">{{ $l->keterangan }}</span></td>
                            <td class="pe-3 small text-secondary">Sistem / Admin</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                Belum ada catatan mutasi (log) untuk bahan ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 py-3">
            {{-- PAGINATION --}}
            <div class="d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection