@extends('layouts.app')

@section('content')
<style>
    .table-excel { border: 2px solid #000; width: 100%; border-collapse: collapse; }
    .table-excel th, .table-excel td { border: 1px solid #000; padding: 10px; text-align: center; }
    .table-excel th { background-color: #f2f2f2 !important; font-weight: bold; }

    @media print {
        @page { size: landscape; margin: 10mm; } 
        .sidebar, .d-print-none, .btn, .sidebar-header { display: none !important; }
        .main-wrapper, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body { background: white !important; font-family: "Arial", sans-serif; }
        .bg-warning { background-color: #ffc107 !important; -webkit-print-color-adjust: exact; }
        .bg-info { background-color: #0dcaf0 !important; -webkit-print-color-adjust: exact; }
        .bg-success { background-color: #198754 !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-print-none d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">🖨️ Siap Cetak: {{ $order->kode_order }}</h2>
        <button onclick="window.print()" class="btn btn-success fw-bold">PRINT SEKARANG</button>
    </div>

    <div class="text-center mb-4">
        <h2 class="fw-bold text-uppercase">Detail Breakdown Production Order</h2>
        <h4>{{ $order->kode_order }} - {{ $order->produk }}</h4>
    </div>

    <table class="table-excel">
        <thead>
            <tr>
                <th width="25%">DETAIL PO</th>
                <th width="15%">SIZE</th>
                <th width="12%">TARGET</th>
                <th class="bg-warning">SEWING</th>
                <th class="bg-info text-white">FINISH</th>
                <th class="bg-success text-white">QC</th>
                <th width="12%">SISA</th>
            </tr>
        </thead>
        <tbody>
            @php $sizes = ['s', 'm', 'l', 'xl']; $first = true; @endphp
            @foreach($sizes as $sz)
                @if($order->{'size_'.$sz} > 0)
                <tr>
                    @if($first)
                    <td rowspan="4" class="text-start ps-3 fw-bold align-middle">
                        {{ $order->kode_order }}<br>
                        <small class="text-muted fw-normal">{{ $order->produk }}</small>
                        @php $first = false; @endphp
                    </td>
                    @endif
                    <td class="fw-bold text-uppercase">SIZE {{ $sz }}</td>
                    <td>{{ number_format($order->{'size_'.$sz}) }}</td>
                    <td>{{ number_format($order->{'sewing_'.$sz}) }}</td>
                    <td>{{ number_format($order->{'finishing_'.$sz}) }}</td>
                    <td class="text-success fw-bold">{{ number_format($order->{'qc_'.$sz}) }}</td>
                    @php $sisa = $order->{'size_'.$sz} - $order->{'qc_'.$sz}; @endphp
                    <td class="fw-bold {{ $sisa > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($sisa) }}
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection