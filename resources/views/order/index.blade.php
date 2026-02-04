@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">📊 Data Order Produksi</h2>
        <a href="/order/create" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Order Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif
    
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Kode PO</th>
                            <th>Model</th> {{-- KOLOM BARU --}}
                            <th>Customer / Produk</th>
                            <th>Target & Sisa</th>
                            <th>Proses (Pcs)</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                        <tr>
                            <td class="ps-3">
                                <a href="/order/{{ $o->id }}/detail" class="fw-bold text-primary text-decoration-none">
                                    {{ $o->kode_order }}
                                </a>
                            </td>
                            {{-- LOGIKA TAMPILIN GAMBAR MODEL --}}
                            <td>
                                @if($o->image)
                                    <img src="{{ asset('uploads/orders/' . $o->image) }}" 
                                         class="img-thumbnail shadow-sm" 
                                         style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                                         data-bs-toggle="modal" data-bs-target="#zoomImg{{ $o->id }}">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 50px; height: 50px; font-size: 10px;">
                                        No Image
                                    </div>
                                @endif
                            </td>
                            <td>
                                <b>{{ $o->customer }}</b><br>
                                <small class="text-muted">{{ $o->produk }}</small>
                            </td>
                            <td>
                                <small class="d-block text-muted">Awal: {{ $o->jumlah }}</small>
                                <small class="d-block text-danger fw-bold">Sisa: {{ $o->qty_order }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <small class="badge bg-secondary">J: {{ $o->qty_produksi }}</small>
                                    <small class="badge bg-info text-dark">F: {{ $o->qty_finishing }}</small>
                                    <small class="badge bg-success">Q: {{ $o->qty_qc }}</small>
                                </div>
                            </td>
                            <td style="width: 150px;">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar {{ $o->progress == 100 ? 'bg-success' : 'bg-primary' }}" 
                                         role="progressbar" style="width: {{ $o->progress }}%"></div>
                                </div>
                                <small class="text-dark fw-bold">{{ round($o->progress) }}%</small>
                            </td>
                            <td>
                                @php
                                    $color = 'bg-warning text-dark';
                                    if($o->status == 'selesai') $color = 'bg-success';
                                    if($o->status == 'finishing') $color = 'bg-info text-dark';
                                    if($o->status == 'qc') $color = 'bg-primary';
                                @endphp
                                <span class="badge {{ $color }}" style="font-size: 10px;">
                                    {{ strtoupper($o->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalInput{{ $o->id }}">
                                        Input
                                    </button>
                                    <a href="/order/{{ $o->id }}/edit" class="btn btn-sm btn-outline-warning">
                                        Size
                                    </a>
                                </div>
                            </td>
                        </tr>

                        {{-- MODAL ZOOM GAMBAR --}}
                        @if($o->image)
                        <div class="modal fade" id="zoomImg{{ $o->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 bg-transparent">
                                    <div class="modal-body p-0 text-center">
                                        <img src="{{ asset('uploads/orders/' . $o->image) }}" class="img-fluid rounded shadow-lg">
                                        <p class="text-white mt-2">{{ $o->kode_order }} - {{ $o->produk }}</p>
                                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection