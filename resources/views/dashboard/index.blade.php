@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <p class="text-muted">Selamat datang di sistem EMTY</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pesanan</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $orders->count() }} Order</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">Monitoring Produksi Terbaru</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Order (Klik Detail)</th>
                            <th>Pelanggan</th>
                            <th style="width: 35%;">Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $item)
                        <tr>
                            <td class="align-middle">
                                <a href="/order/{{ $item->id }}/detail" class="fw-bold text-primary text-decoration-none">
                                    {{ $item->kode_order }}
                                </a>
                            </td>
                            <td class="align-middle">{{ $item->customer }}</td>
                            <td class="align-middle">
                                <div class="progress mb-1" style="height: 12px;">
                                    <div class="progress-bar {{ $item->progress == 100 ? 'bg-success' : 'bg-info progress-bar-striped progress-bar-animated' }}" 
                                         role="progressbar" style="width: {{ $item->progress }}%">
                                         {{ round($item->progress) }}%
                                    </div>
                                </div>
                                <small class="text-muted d-block">
                                    Target: {{ $item->jumlah }} | Jahit: {{ $item->qty_produksi }} | QC: {{ $item->qty_qc }}
                                </small>
                            </td>
                            <td class="align-middle">
                                <span class="badge {{ $item->status == 'selesai' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-center">
            <a href="/order" class="btn btn-sm btn-link text-decoration-none">Kelola Semua Order Produksi &rarr;</a>
        </div>
    </div>
</div>
@endsection