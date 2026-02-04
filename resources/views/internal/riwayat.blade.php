@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold m-0 text-dark"><i class="fas fa-history me-2"></i>Riwayat Pesan Bantuan Divisi</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Divisi</th>
                            <th>Pesan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($semua_pesan as $p)
                        <tr>
                            <td>{{ $p->created_at->format('d M Y, H:i') }}</td>
                            <td><span class="badge bg-warning text-dark">{{ strtoupper($p->divisi) }}</span></td>
                            <td class="text-start">{{ $p->pesan }}</td>
                            <td>
                                @if($p->is_read)
                                    <span class="badge bg-success"><i class="fas fa-check-double me-1"></i> Sudah Dibaca</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $semua_pesan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection