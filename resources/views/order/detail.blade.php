@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary mb-0">Detail Produksi: {{ $order->kode_order }}</h2>
        <a href="{{ route('order.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row mb-4">
        {{-- 1. KOTAK INFORMASI PO --}}
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <i class="fas fa-info-circle me-1"></i> Informasi PO
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Customer:</strong> {{ $order->customer }}</p>
                    <p class="mb-2"><strong>Produk:</strong> {{ $order->produk }}</p>
                    <p class="mb-2"><strong>Target Total:</strong> <span class="badge bg-dark">{{ number_format($order->jumlah) }} Pcs</span></p>
                    <hr>
                    <p class="mb-0"><strong>Status:</strong> 
                        @php
                            $color = 'bg-warning text-dark';
                            if($order->status == 'selesai') $color = 'bg-success text-white';
                            if($order->status == 'finishing') $color = 'bg-info text-dark';
                        @endphp
                        <span class="badge {{ $color }}">{{ strtoupper($order->status) }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- 2. KOTAK MODEL ACUAN --}}
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <i class="fas fa-tshirt me-1"></i> Model Produk
                </div>
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    @if($order->image)
                        <img src="{{ asset('uploads/orders/' . $order->image) }}" 
                             class="img-fluid rounded border shadow-sm" 
                             style="max-height: 150px; cursor: pointer;" 
                             data-bs-toggle="modal" data-bs-target="#imageModal">
                        <small class="text-muted d-block mt-2"><i class="fas fa-search-plus"></i> Klik Zoom</small>
                    @else
                        <div class="text-muted py-4">
                            <i class="fas fa-image fa-2x d-block mb-1"></i>
                            <small>Tidak ada foto model</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 3. KOTAK SIZE CHART --}}
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
                <div class="card-header bg-info text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <i class="fas fa-ruler-combined me-1"></i> Size Chart
                </div>
                <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                    @if($order->size_chart)
                        <img src="{{ asset('uploads/charts/' . $order->size_chart) }}" 
                             class="img-fluid rounded border shadow-sm" 
                             style="max-height: 150px; cursor: pointer;" 
                             data-bs-toggle="modal" data-bs-target="#chartModal">
                        <small class="text-muted d-block mt-2"><i class="fas fa-search-plus"></i> Klik Zoom</small>
                    @else
                        <div class="text-muted py-4">
                            <i class="fas fa-ruler fa-2x d-block mb-1"></i>
                            <small>Belum ada size chart</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL RINCIAN PER WARNA --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-secondary text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <i class="fas fa-list me-1"></i> Rincian Pesanan Per Warna & Size
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4">Warna / Varian</th>
                                    <th>S</th>
                                    <th>M</th>
                                    <th>L</th>
                                    <th>XL</th>
                                    <th class="bg-light fw-bold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->variants as $v)
                                <tr>
                                    <td class="text-start ps-4 fw-bold text-primary">
                                        <i class="fas fa-palette me-2"></i>{{ $v->warna }}
                                    </td>
                                    <td>{{ $v->s }}</td>
                                    <td>{{ $v->m }}</td>
                                    <td>{{ $v->l }}</td>
                                    <td>{{ $v->xl }}</td>
                                    <td class="fw-bold bg-light text-dark">{{ number_format($v->total) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-5 text-muted text-center">
                                        Belum ada rincian warna. Klik <b>Edit</b> untuk mengisi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            {{-- Baris Total --}}
                            @if($order->variants->count() > 0)
                            <tfoot class="table-dark fw-bold">
                                <tr>
                                    <td class="text-start ps-4">TOTAL KESELURUHAN</td>
                                    <td>{{ $order->variants->sum('s') }}</td>
                                    <td>{{ $order->variants->sum('m') }}</td>
                                    <td>{{ $order->variants->sum('l') }}</td>
                                    <td>{{ $order->variants->sum('xl') }}</td>
                                    <td class="text-warning">{{ number_format($order->variants->sum('total')) }} Pcs</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white p-4" style="border-radius: 0 0 15px 15px;">
                    <h6 class="fw-bold mb-3">Progress Produksi Keseluruhan:</h6>
                    <div class="progress" style="height: 30px; border-radius: 15px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated {{ $order->progress == 100 ? 'bg-success' : 'bg-primary' }}" 
                             role="progressbar" style="width: {{ $order->progress }}%">
                             <span class="fw-bold">{{ round($order->progress) }}% Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL ZOOM MODEL --}}
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent text-center">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal"></button>
            <img src="{{ asset('uploads/orders/' . $order->image) }}" class="img-fluid rounded shadow-lg">
        </div>
    </div>
</div>

{{-- MODAL ZOOM CHART --}}
<div class="modal fade" id="chartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent text-center">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal"></button>
            <img src="{{ asset('uploads/charts/' . $order->size_chart) }}" class="img-fluid rounded shadow-lg">
        </div>
    </div>
</div>
@endsection