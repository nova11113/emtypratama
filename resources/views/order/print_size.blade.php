@extends('layouts.app')

@section('content')
<style>
    /* --- STYLE EXCEL UNTUK PRINT DETAIL SIZE --- */
    .table-excel { border: 2px solid #000; width: 100%; border-collapse: collapse; }
    .table-excel th, .table-excel td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 13px; }
    .table-excel th { background-color: #f2f2f2 !important; color: #000; font-weight: bold; }

    .img-print { max-height: 150px; border: 1px solid #000; padding: 5px; border-radius: 5px; }

    @media print {
        @page { size: landscape; margin: 10mm; } 
        .sidebar, .d-print-none, .btn, .alert { display: none !important; }
        .main-wrapper, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body { background: white !important; font-family: "Arial", sans-serif; }
        .table-excel { border: 2px solid #000 !important; }
        .table-excel th, .table-excel td { border: 1px solid #000 !important; color: black !important; }
    }
</style>

<div class="container-fluid py-4">
    {{-- Tombol Navigasi --}}
    <div class="d-print-none d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0 fw-bold">📄 Siap Cetak Breakdown: {{ $order->kode_order }}</h2>
        <div>
            <a href="javascript:history.back()" class="btn btn-secondary shadow-sm">⬅️ Kembali</a>
            <button onclick="window.print()" class="btn btn-success fw-bold shadow-sm">🖨️ PRINT SEKARANG</button>
        </div>
    </div>

    {{-- Header & Info Produk --}}
    <div class="row align-items-center mb-4">
        <div class="col-8 text-start">
            <h2 class="fw-bold mb-0">SURAT PERINTAH KERJA (SPK)</h2>
            <h4 class="text-primary">{{ $order->kode_order }} - {{ $order->produk }}</h4>
            <p class="mb-0 text-muted">Customer: <strong>{{ $order->customer }}</strong> | Tanggal: {{ date('d/m/Y') }}</p>
        </div>
        <div class="col-4 text-end">
            @if($order->image)
                <img src="{{ asset('uploads/orders/' . $order->image) }}" class="img-print" alt="Model">
            @endif
        </div>
    </div>

    {{-- Tabel Breakdown Per Warna & Size --}}
    <table class="table-excel">
        <thead>
            <tr>
                <th>WARNA / VARIAN</th>
                <th width="10%">S</th>
                <th width="10%">M</th>
                <th width="10%">L</th>
                <th width="10%">XL</th>
                <th width="15%" class="bg-light text-dark text-white">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->variants as $v)
            <tr>
                <td class="text-start ps-3 fw-bold bg-light">{{ strtoupper($v->warna) }}</td>
                <td>{{ $v->s > 0 ? number_format($v->s) : '-' }}</td>
                <td>{{ $v->m > 0 ? number_format($v->m) : '-' }}</td>
                <td>{{ $v->l > 0 ? number_format($v->l) : '-' }}</td>
                <td>{{ $v->xl > 0 ? number_format($v->xl) : '-' }}</td>
                <td class="fw-bold bg-light">{{ number_format($v->total) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-4">Data rincian warna belum diisi.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot class="fw-bold" style="background-color: #eee !important;">
            <tr>
                <td class="text-start ps-3">TOTAL KESELURUHAN PCS</td>
                <td>{{ number_format($order->variants->sum('s')) }}</td>
                <td>{{ number_format($order->variants->sum('m')) }}</td>
                <td>{{ number_format($order->variants->sum('l')) }}</td>
                <td>{{ number_format($order->variants->sum('xl')) }}</td>
                <td class="bg-dark text-white">{{ number_format($order->variants->sum('total')) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Footer Tanda Tangan --}}
    <div class="row mt-5">
        <div class="col-4 text-center">
            <p>Dibuat Oleh,</p>
            <br><br><br>
            <p class="fw-bold">( Admin Produksi )</p>
        </div>
        <div class="col-4 text-center">
            {{-- Spacer --}}
        </div>
        <div class="col-4 text-center">
            <p>Diterima Oleh,</p>
            <br><br><br>
            <p class="fw-bold">( Kepala Produksi )</p>
        </div>
    </div>
</div>
@endsection