<div class="sidebar-heading">Utama</div>
<li class="nav-item">
    <a class="nav-link" href="{{ route('dashboard') }}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>
</li>

<hr class="sidebar-divider">

<div class="sidebar-heading">Procurement</div>
<li class="nav-item">
    <a class="nav-link" href="{{ route('procurement.index') }}">
        <i class="fas fa-fw fa-shopping-cart"></i>
        <span>Pembelian Bahan</span>
    </a>
</li>

<hr class="sidebar-divider">

<div class="sidebar-heading">Inventory</div>
<li class="nav-item">
    <a class="nav-link" href="{{ route('inventory.index') }}">
        <i class="fas fa-fw fa-boxes"></i>
        <span>Stok Bahan Baku</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('inventory.logs') }}">
        <i class="fas fa-fw fa-history"></i>
        <span>Mutasi / Kartu Stok</span>
    </a>
</li>

<hr class="sidebar-divider">

<div class="sidebar-heading">Produksi</div>
<li class="nav-item">
    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseProduksi">
        <i class="fas fa-fw fa-factory"></i>
        <span>Divisi Produksi</span>
    </a>
    <div id="collapseProduksi" class="collapse" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="{{ route('cutting.index') }}">Cutting</a>
            <a class="collapse-item" href="{{ route('sewing.index') }}">Sewing</a>
            <a class="collapse-item" href="{{ route('finishing.index') }}">Finishing</a>
            <a class="collapse-item" href="{{ route('qc.index') }}">Quality Control</a>
        </div>
    </div>
</li>

<hr class="sidebar-divider">

<div class="sidebar-heading">Logistik</div>
<li class="nav-item">
    <a class="nav-link" href="{{ route('gudang.index') }}">
        <i class="fas fa-fw fa-warehouse"></i>
        <span>Gudang Barang Jadi</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('order.shipmentHistory') }}">
        <i class="fas fa-fw fa-truck"></i>
        <span>Riwayat Pengiriman</span>
    </a>
</li>