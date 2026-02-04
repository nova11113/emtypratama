<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM EMTY - GARMENT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Styling Khusus Sub-Menu (Anak) */
.sidebar .collapse a {
    background: #1a252f; /* Warna lebih gelap dari sidebar utama */
    padding-left: 45px !important; /* Biar lebih menjorok ke dalam */
    font-size: 13px;
    border-left: none; /* Hilangkan garis biru default */
}

.sidebar .collapse a:hover {
    background: #243342;
    color: #3498db;
    padding-left: 50px !important; /* Animasi geser dikit pas hover */
}

/* Biar icon panahnya gak kegedean */
.sidebar .fa-chevron-down {
    font-size: 10px;
    transition: transform 0.3s ease;
}
        body { margin:0; font-family: 'Segoe UI', Arial, sans-serif; background:#f4f7f6; overflow-x: hidden; }
        
        /* Sidebar Styling */
        .sidebar { width: 250px; height: 100vh; background: #2c3e50; position: fixed; top: 0; left: 0; z-index: 1050; color: white; transition: all 0.3s ease; overflow-y: auto; }
        .sidebar-header { padding: 20px; text-align: center; background: #1a252f; font-weight: bold; border-bottom: 1px solid #34495e; }
        .sidebar a { display: block; padding: 12px 20px; color: #bdc3c7; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active-link { background: #34495e; color: white; padding-left: 25px; border-left: 4px solid #3498db; }
        .sidebar .section-title { padding: 15px 20px 5px; font-size: 11px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Wrapper Styling */
        .main-wrapper { margin-left: 250px; min-height: 100vh; width: calc(100% - 250px); padding: 25px; transition: all 0.3s ease; }
        
        /* Navbar Mobile */
        .mobile-nav { display: none; background: #2c3e50; color: white; padding: 10px 15px; position: sticky; top: 0; z-index: 1040; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }

        /* Responsive Settings */
        @media (max-width: 768px) {
            .sidebar { left: -250px; } /* Sembunyi ke kiri di HP */
            .sidebar.active { left: 0; } /* Muncul pas aktif */
            .main-wrapper { margin-left: 0; width: 100%; padding: 15px; }
            .mobile-nav { display: flex; align-items: center; justify-content: space-between; }
            
            /* Overlay saat sidebar muncul di HP */
            .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1045; }
            .sidebar-overlay.active { display: block; }
        }

        .text-danger { color: #e74c3c !important; }
        .card { border-radius: 10px; border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="overlay"></div>

<div class="mobile-nav">
    <h5 class="mb-0">GARMENT EMTY</h5>
    <button class="btn btn-outline-light btn-sm" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h4 class="mb-0">GARMENT EMTY</h4>
    </div>
    
    <div class="mt-3">
        <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active-link' : '' }}">🏠 Dashboard</a>
        <a href="{{ route('internal.history') }}" class="{{ request()->is('internal-request/history') ? 'active-link' : '' }}">📜 Riwayat Bantuan</a>

        @if(strtolower(Auth::user()->role) == 'admin')
            <div class="section-title">Manajemen Utama</div>
            <a href="{{ route('order.index') }}" class="{{ request()->is('order') ? 'active-link' : '' }}">📦 Order Produksi</a>
            <a href="{{ route('employee.index') }}" class="{{ request()->is('karyawan*') ? 'active-link' : '' }}">👷 Data Karyawan</a>
            
            <div class="nav-item">
                <a class="d-flex justify-content-between align-items-center {{ request()->is('produksi/report*') ? 'active-link' : '' }}" 
                   data-bs-toggle="collapse" 
                   href="#collapseLaporan" 
                   role="button">
                    <span>📊 Laporan Produksi</span>
                    <i class="fas fa-chevron-down small"></i>
                </a>
                
                <div class="collapse {{ request()->is('produksi/report*') ? 'show' : '' }}" id="collapseLaporan" style="background: #1a252f;">
                    <a href="{{ route('order.report') }}" class="py-2 ps-5 {{ request()->is('produksi/report') ? 'text-white fw-bold' : '' }}" style="font-size: 13px;">
                        📅 Rekap Harian
                    </a>
                    <a href="#" class="py-2 ps-5 text-muted" style="font-size: 13px;">🗓️ Rekap Mingguan</a>
                </div>
            </div>
        @endif

        @if(in_array(strtolower(Auth::user()->role), ['admin', 'gudang', 'purchasing']))
    <div class="section-title">Logistik & Inventory</div>
    <a href="{{ route('inventory.index') }}" class="{{ request()->is('inventory') ? 'active-link' : '' }}">🧶 Stok Gudang Bahan</a>
    
    <a href="{{ route('inventory.request.index') }}" class="{{ request()->is('inventory/request*') ? 'active-link' : '' }}">📩 Permintaan Bahan</a>
    
    <a href="{{ route('procurement.index') }}" class="{{ request()->is('procurement*') ? 'active-link' : '' }}">🛒 Pembelian Bahan</a>
    <a href="{{ route('gudang.index') }}" class="{{ request()->is('gudang*') ? 'active-link' : '' }}">📦 Stok Gudang Jadi</a>
    <a href="{{ route('order.shipmentHistory') }}" class="{{ request()->is('shipment-history*') ? 'active-link' : '' }}">📜 Riwayat Pengiriman</a>
@endif

        <div class="section-title">Divisi Produksi</div>
        @if(in_array(strtolower(Auth::user()->role), ['admin', 'cutting']))
            <a href="{{ route('cutting.index') }}" class="{{ request()->is('produksi/cutting') ? 'active-link' : '' }}">✂️ Bagian Cutting</a>
        @endif
        
        @if(in_array(strtolower(Auth::user()->role), ['admin', 'sewing']))
            <a href="{{ route('sewing.index') }}" class="{{ request()->is('produksi/sewing') ? 'active-link' : '' }}">🪡 Bagian Sewing</a>
        @endif
        
        @if(in_array(strtolower(Auth::user()->role), ['admin', 'finishing']))
            <a href="{{ route('finishing.index') }}" class="{{ request()->is('produksi/finishing') ? 'active-link' : '' }}">✨ Bagian Finishing</a>
        @endif
        
        @if(in_array(strtolower(Auth::user()->role), ['admin', 'qc']))
            <a href="{{ route('qc.index') }}" class="{{ request()->is('produksi/qc') ? 'active-link' : '' }}">🔍 Bagian QC (Quality Control)</a> 
        @endif

        <div class="section-title">Akun</div>
        <a href="/logout" class="text-danger mt-4">🚪 Logout</a>
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

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('overlay');

    // Fungsi Buka/Tutup Sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);

    // Otomatis tutup sidebar kalau link diklik (buat HP)
    const navLinks = document.querySelectorAll('.sidebar a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleSidebar();
            }
        });
    });
</script>

</body>
</html>