<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM EMTY - GARMENT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin:0; font-family: 'Segoe UI', Arial, sans-serif; background:#f4f7f6; overflow-x: hidden; }
        
        .sidebar {
            width: 250px; 
            height: 100vh;
            background: #2c3e50;
            position: fixed; 
            top: 0;
            left: 0;
            z-index: 1000;
            color: white;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            background: #1a252f;
            font-weight: bold;
            border-bottom: 1px solid #34495e;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .sidebar a:hover, .sidebar a.active-link {
            background: #34495e;
            color: white;
            padding-left: 25px;
            border-left: 4px solid #3498db;
        }

        .sidebar .section-title {
            padding: 15px 20px 5px;
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .main-wrapper {
            margin-left: 250px; 
            min-height: 100vh;
            width: calc(100% - 250px);
            padding: 25px;
            transition: all 0.3s;
        }

        .text-danger { color: #e74c3c !important; }
        .card { border-radius: 10px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }

        @media (max-width: 768px) {
            .sidebar { margin-left: -250px; }
            .main-wrapper { margin-left: 0; width: 100%; }
            .sidebar.active { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">GARMENT EMTY</h4>
    </div>
    
    <div class="mt-3">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active-link' : '' }}">🏠 Dashboard</a>

        {{-- MENU ADMIN --}}
        @if(strtolower(Auth::user()->role) == 'admin')
            <div class="section-title">Manajemen Utama</div>
            <a href="/order" class="{{ request()->is('order') ? 'active-link' : '' }}">📦 Order Produksi</a>
            <a href="/karyawan" class="{{ request()->is('karyawan') ? 'active-link' : '' }}">👷 Data Karyawan</a>
            <a href="{{ route('order.report') }}" class="{{ request()->is('order/report') ? 'active-link' : '' }}">📊 Laporan Produksi</a>
        @endif

        

        <div class="section-title">Divisi Produksi</div>

        @if(in_array(strtolower(Auth::user()->role), ['admin', 'cutting']))
            <a href="{{ route('cutting.index') }}" class="{{ request()->is('cutting') ? 'active-link' : '' }}">✂️ Bagian Cutting</a>
        @endif

        @if(in_array(strtolower(Auth::user()->role), ['admin', 'sewing']))
            <a href="/sewing" class="{{ request()->is('sewing') ? 'active-link' : '' }}">🪡 Bagian Sewing</a>
        @endif

        @if(in_array(strtolower(Auth::user()->role), ['admin', 'finishing']))
            <a href="/finishing" class="{{ request()->is('finishing') ? 'active-link' : '' }}">✨ Bagian Finishing</a>
        @endif

        @if(in_array(strtolower(Auth::user()->role), ['admin', 'qc']))
            <a href="/qc" class="{{ request()->is('qc') ? 'active-link' : '' }}">🔍 Bagian QC (Quality Control)</a> 
        @endif

        {{-- MENU LOGISTIK & GUDANG --}}
        <div class="section-title">Logistik & Gudang</div>
        @if(in_array(strtolower(Auth::user()->role), ['admin', 'gudang']))
            {{-- Stok Gudang (Tempat buat pengiriman baru) --}}
            <a href="{{ route('gudang.index') }}" class="{{ request()->is('gudang*') ? 'active-link' : '' }}">📦 Stok Gudang Jadi</a>
            
            {{-- Riwayat Pengiriman (Tempat liat & cetak ulang Surat Jalan) --}}
            <a href="{{ route('order.shipmentHistory') }}" class="{{ request()->is('shipment-history*') ? 'active-link' : '' }}">📜 Riwayat Pengiriman</a>
        @endif

        <div class="section-title">Akun</div>
        <a href="/logout" class="text-danger">🚪 Logout</a>
    </div>
</div>

<div class="main-wrapper">
    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>