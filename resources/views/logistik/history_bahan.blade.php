@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0 text-dark"><i class="fas fa-exchange-alt me-2 text-primary"></i>Log Mutasi Bahan Baku</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-success btn-sm"><i class="fas fa-file-excel me-1"></i> Export Excel</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal & Jam</th>
                            <th>Nama Bahan</th>
                            <th>Keterangan / Referensi</th>
                            <th class="text-center">Masuk</th>
                            <th class="text-center">Keluar</th>
                            <th class="text-center bg-light">Sisa Saldo</th>
                            <th>Operator</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td class="ps-3 small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="fw-bold text-dark">{{ $log->bahan->nama_bahan }}</span></td>
                            <td><small class="text-muted">{{ $log->keterangan }}</small></td>
                            <td class="text-center">
                                @if($log->aksi == 'masuk')
                                    <span class="text-success fw-bold">+{{ number_format($log->jumlah, 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($log->aksi == 'keluar')
                                    <span class="text-danger fw-bold">-{{ number_format($log->jumlah, 2) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center bg-light fw-bold text-primary">
                                {{ number_format($log->saldo_akhir, 2) }}
                            </td>
                            <td><span class="badge bg-secondary">{{ $log->operator }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection