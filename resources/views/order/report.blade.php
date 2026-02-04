@extends('layouts.app')

@section('content')
<style>
    /* STYLE EXCEL MODE */
    .table-excel { border: 2px solid #333; }
    .table-excel th { background-color: #f2f2f2 !important; color: #000; border: 1px solid #ccc; font-weight: bold; }
    .table-excel td { border: 1px solid #ccc; }
    
    /* Link PO Biru & Pointer */
    .po-link { text-decoration: none; color: #0d6efd !important; font-weight: bold; cursor: pointer; }
    .po-link:hover { text-decoration: underline !important; color: #0a58ca !important; }

    /* Baris breakdown size disembunyikan dulu */
    .breakdown-row { display: none; background-color: #fafafa; }

    /* Kotak Ringkasan Dashboard Style */
    .stat-box { border-radius: 8px; overflow: hidden; display: flex; align-items: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); height: 90px; border: 1px solid #eee; }
    .stat-icon { width: 70px; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 28px; }
    .stat-data { padding: 10px 20px; flex-grow: 1; }
    .stat-data h3 { margin: 0; font-weight: 800; font-size: 24px; line-height: 1; }
    .stat-data small { color: #666; font-size: 11px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; }

    @media print {
        @page { size: landscape; margin: 10mm; } 
        .sidebar, .d-print-none, .btn, .sidebar-header, .card-filter { display: none !important; }
        .main-wrapper, .container-fluid { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body { background: white !important; }
        .table-excel { width: 100% !important; border-collapse: collapse !important; border: 2px solid #000 !important; }
        .table-excel th, .table-excel td { border: 1px solid #000 !important; padding: 5px !important; font-size: 11px !important; }
        /* Pas diprint, semua breakdown otomatis muncul */
        .breakdown-row { display: table-row !important; }
        .bg-warning { background-color: #ffc107 !important; -webkit-print-color-adjust: exact; }
        .bg-info { background-color: #0dcaf0 !important; -webkit-print-color-adjust: exact; }
        .bg-success { background-color: #198754 !important; -webkit-print-color-adjust: exact; }
    }
</style>

<div class="container-fluid py-4">
    {{-- JUDUL & TOMBOL PRINT --}}
    <div class="d-print-none d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0 fw-bold">📊 Laporan Produksi Terpadu</h2>
        <button onclick="window.print()" class="btn btn-success px-4 fw-bold shadow-sm">
            <i class="fas fa-print me-2"></i> PRINT REKAP LENGKAP
        </button>
    </div>

    {{-- FILTER SECTION --}}
    <div class="card shadow-sm mb-4 d-print-none border-0 card-filter bg-light">
        <div class="card-body">
            <form action="{{ route('order.report') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">📊 MODE REKAP</label>
                    <select name="periode" class="form-select border-primary" onchange="this.form.submit()">
                        <option value="harian" {{ request('periode') == 'harian' ? 'selected' : '' }}>📅 Rekap Harian</option>
                        <option value="bulanan" {{ request('periode') == 'bulanan' ? 'selected' : '' }}>📊 Rekap Bulanan</option>
                        <option value="semua" {{ request('periode') == 'semua' ? 'selected' : '' }}>📁 Semua Data (Aktif)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">📅 PILIH TANGGAL/BULAN</label>
                    <input type="{{ request('periode') == 'bulanan' ? 'month' : 'date' }}" 
                           name="tanggal" class="form-control" 
                           value="{{ request('tanggal', date('Y-m-d')) }}"
                           onchange="this.form.submit()">
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge bg-dark p-2">
                        FILTER: {{ strtoupper(request('periode', 'harian')) }} 
                        ({{ \Carbon\Carbon::parse(request('tanggal', date('Y-m-d')))->translatedFormat('d M Y') }})
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- RINGKASAN OUTPUT (DARI DATA REAL LOG) --}}
    <div class="row g-3 mb-4">
        @php
            // Mengambil angka dari variabel $rekap yang kita kirim dari Controller
            $summary = [
                ['Sewing (Jahit)', $rekap['sewing'] ?? 0, '#f1b400', 'fa-tshirt'],
                ['Finishing', $rekap['finishing'] ?? 0, '#00c0ef', 'fa-magic'],
                ['QC Pass', $rekap['qc'] ?? 0, '#198754', 'fa-check-circle'],
                ['Sisa Target', $rekap['sisa'] ?? 0, '#dc3545', 'fa-hourglass-half']
            ];
        @endphp

        @foreach($summary as $s)
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon" style="background: {{ $s[2] }}">
                    <i class="fas {{ $s[3] }}"></i>
                </div>
                <div class="stat-data">
                    <small>{{ $s[0] }}</small>
                    <h3 style="color: {{ $s[2] }}">{{ number_format($s[1]) }}</h3>
                    <div class="text-muted" style="font-size: 10px;">Hari Ini (Pcs)</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- HEADER PRINT --}}
    <div class="report-header text-center mb-4">
        <h2 class="fw-bold mb-0">REKAPITULASI & BREAKDOWN PRODUKSI</h2>
        <h4 class="fw-bold text-muted">GARMENT EMTY</h4>
        <p class="mt-2">Periode: {{ \Carbon\Carbon::parse(request('tanggal', date('Y-m-d')))->translatedFormat('d F Y') }}</p>
    </div>

    {{-- TABEL UTAMA --}}
    <table class="table table-excel text-center align-middle shadow-sm bg-white">
        <thead>
            <tr>
                <th width="20%">NOMOR PO / DETAIL</th>
                <th width="12%">SIZE</th>
                <th width="10%">TARGET</th>
                <th style="background-color: #f1b400 !important; color: #000;">SEWING</th>
                <th style="background-color: #00c0ef !important; color: #fff;">FINISHING</th>
                <th style="background-color: #198754 !important; color: #fff;">QC (SIAP)</th>
                <th width="12%">SISA TARGET</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $o)
                {{-- BARIS UTAMA PER PO --}}
                <tr class="table-light">
                    <td class="text-start ps-3 fw-bold border-end">
                        {{-- LINK PO AKTIF --}}
                        <a href="{{ route('order.print_size', $o->id) }}" class="po-link d-print-none">
                            {{ $o->kode_order }} <i class="fas fa-external-link-alt ms-1" style="font-size: 10px;"></i>
                        </a>
                        <span class="d-none d-print-block">{{ $o->kode_order }}</span>

                        {{-- Tombol Dropdown --}}
                        <span class="ms-2 d-print-none text-muted" style="cursor:pointer;" onclick="toggleBreakdown('{{ $o->id }}')">
                            <i class="fas fa-chevron-circle-down"></i>
                        </span>
                        
                        <br><small class="text-muted fw-normal">{{ $o->produk }}</small>
                    </td>
                    <td class="fw-bold text-muted small italic bg-light">TOTAL PO</td>
                    <td class="fw-bold bg-light">{{ number_format($o->jumlah) }}</td>
                    <td class="fw-bold bg-light" style="color: #d4a017">{{ number_format($o->qty_produksi) }}</td>
                    <td class="fw-bold bg-light text-info">{{ number_format($o->qty_finishing) }}</td>
                    <td class="fw-bold bg-light text-success">{{ number_format($o->qty_qc) }}</td>
                    <td class="fw-bold bg-light {{ $o->qty_order > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($o->qty_order) }}
                    </td>
                </tr>

                {{-- BARIS BREAKDOWN SIZE --}}
                @foreach(['s', 'm', 'l', 'xl'] as $sz)
                    @if($o->{'size_'.$sz} > 0)
                    <tr class="breakdown-row row-id-{{ $o->id }}">
                        <td class="text-end pe-4 text-muted small border-end">↳ Detail Size:</td>
                        <td class="fw-bold text-uppercase bg-white">Size {{ $sz }}</td>
                        <td class="bg-white">{{ number_format($o->{'size_'.$sz}) }}</td>
                        <td class="bg-white">{{ number_format($o->{'sewing_'.$sz}) }}</td>
                        <td class="bg-white">{{ number_format($o->{'finishing_'.$sz}) }}</td>
                        <td class="bg-white text-success fw-bold">{{ number_format($o->{'qc_'.$sz}) }}</td>
                        @php $sisaSz = $o->{'size_'.$sz} - $o->{'qc_'.$sz}; @endphp
                        <td class="bg-white fw-bold {{ $sisaSz > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($sisaSz) }}
                        </td>
                    </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
        <tfoot class="table-dark fw-bold">
            <tr>
                <td colspan="2" class="text-end pe-3">GRAND TOTAL</td>
                <td>{{ number_format($orders->sum('jumlah')) }}</td>
                <td>{{ number_format($orders->sum('qty_produksi')) }}</td>
                <td>{{ number_format($orders->sum('qty_finishing')) }}</td>
                <td>{{ number_format($orders->sum('qty_qc')) }}</td>
                <td>{{ number_format($orders->sum('qty_order')) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<script>
    function toggleBreakdown(orderId) {
        let rows = document.querySelectorAll('.row-id-' + orderId);
        rows.forEach(row => {
            row.style.display = (row.style.display === "table-row") ? "none" : "table-row";
        });
    }
</script>
@endsection