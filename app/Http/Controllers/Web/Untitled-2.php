<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Material;
use App\Models\Purchasing; 
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Dashboard - Statistik Real-Time
     */
    public function dashboard() {
        $orders = Order::latest()->get();
        $total_terkirim = $orders->sum('qty_terkirim') ?? 0;
        $total_order   = ($orders->sum('jumlah') ?? 0) - $total_terkirim;
        $total_qc      = $orders->sum('qty_qc') ?? 0;
        $total_gudang  = $total_qc - $total_terkirim;

        return view('dashboard', [
            'orders' => $orders,
            'total_order' => $total_order,
            'total_cutting' => $orders->sum('qty_cutting'),
            'total_sewing' => $orders->sum('qty_produksi'),
            'total_qc' => $total_qc,
            'total_reject' => $orders->sum('qty_reject'),
            'total_gudang' => $total_gudang
        ]);
    }

    // Pastikan di paling atas ada: use App\Models\Purchasing;

public function procurementStore(Request $request) {
    $request->validate([
        'material_id' => 'required',
        'supplier' => 'required',
        'jumlah' => 'required|numeric',
    ]);

    Purchasing::create([
        'material_id' => $request->material_id,
        'supplier' => $request->supplier,
        'jumlah' => $request->jumlah,
        'status' => 'pending' // Default pending biar gak langsung nambah stok
    ]);

    return back()->with('success', 'Pesanan pembelian berhasil dicatat!');
}
public function inventoryStore(Request $request) {
    $request->validate([
        'nama_bahan' => 'required|string|max:255',
        'satuan' => 'required',
        'minimal_stok' => 'required|numeric'
    ]);

    \App\Models\Material::create([
        'nama_bahan' => $request->nama_bahan,
        'kategori' => $request->kategori,
        'satuan' => $request->satuan,
        'stok' => 0, // Stok awal selalu 0, nanti nambahnya lewat menu Pembelian
        'minimal_stok' => $request->minimal_stok,
    ]);

    return back()->with('success', 'Master bahan baku berhasil dibuat!');
}

// Fungsi supaya pas barang nyampe, stok di Inventory nambah otomatis
public function terimaBahan($id) {
    $pembelian = Purchasing::findOrFail($id);
    
    if($pembelian->status == 'diterima') {
        return back()->with('error', 'Barang ini sudah pernah diterima.');
    }

    // 1. Update status pembelian
    $pembelian->update(['status' => 'diterima']);

    // 2. Tambah stok di Master Bahan
    $material = \App\Models\Material::find($pembelian->material_id);
    $material->increment('stok', $pembelian->jumlah);

    return back()->with('success', 'Stok bahan berhasil ditambahkan!');
}

    public function gunakanBahan(Request $r) {
    $r->validate([
        'material_id' => 'required',
        'jumlah' => 'required|integer',
    ]);

    $material = Material::findOrFail($r->material_id);
    
    // Cek stok cukup gak
    if($material->stok < $r->jumlah) {
        return back()->with('error', 'Stok bahan tidak cukup!');
    }

    // 1. Kurangi stok utama
    $material->decrement('stok', $r->jumlah);

    // 2. Catat di History (Log)
    MaterialLog::create([
        'material_id' => $r->material_id,
        'tipe' => 'keluar',
        'jumlah' => $r->jumlah,
        'keterangan' => 'Dipakai untuk Cutting Order ID: ' . $r->order_id
    ]);

    return back()->with('success', 'Bahan berhasil dipotong dari stok!');
}

    /**
     * Progress Produksi (Cutting, Sewing, Finishing, QC)
     */
    public function updateProgressBulk(Request $request) {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'qty_tambah' => 'required|integer|min:1',
            'tipe' => 'required|in:cutting,produksi,finishing,qc',
            'size_type' => 'required|in:s,m,l,xl'
        ]);

        $order = Order::findOrFail($request->order_id);
        $size = $request->size_type;
        $qty = $request->qty_tambah;

        if ($request->tipe == 'cutting') {
            $target = 'size_' . $size; $current = 'cutting_' . $size;
            if (($order->$current + $qty) > $order->$target) { return back()->with('error', "Gagal! Melebihi target Size " . strtoupper($size)); }
            $order->$current += $qty; $order->qty_cutting += $qty;
        } elseif ($request->tipe == 'produksi') {
            $target = 'cutting_' . $size; $current = 'sewing_' . $size;
            if (($order->$current + $qty) > $order->$target) { return back()->with('error', "Gagal! Kain hasil cutting tidak cukup."); }
            $order->$current += $qty; $order->qty_produksi += $qty;
            $order->qty_order = max(0, $order->jumlah - $order->qty_produksi);
        } elseif ($request->tipe == 'finishing') {
            $target = 'sewing_' . $size; $current = 'finishing_' . $size;
            if (($order->$current + $qty) > $order->$target) { return back()->with('error', "Gagal! Stok jahit tidak cukup."); }
            $order->$current += $qty; $order->qty_finishing += $qty;
        } elseif ($request->tipe == 'qc') {
            $target = 'finishing_' . $size; $colQC = 'qc_' . $size; $colReject = 'qc_reject_' . $size;
            if ((($order->$colQC + $order->$colReject) + $qty) > $order->$target) { return back()->with('error', "Gagal! Sisa finishing tidak cukup."); }
            if ($request->is_reject == 'ya') { $order->$colReject += $qty; $order->qty_reject += $qty; }
            else { $order->$colQC += $qty; $order->qty_qc += $qty; }
        }

        if ($order->jumlah > 0) { $order->progress = min(100, ($order->qty_qc / $order->jumlah) * 100); }
        if ($order->progress >= 100) $order->status = 'selesai';
        elseif ($order->qty_qc > 0) $order->status = 'qc';
        elseif ($order->qty_finishing > 0) $order->status = 'finishing';
        elseif ($order->qty_produksi > 0) $order->status = 'produksi';
        elseif ($order->qty_cutting > 0) $order->status = 'cutting';

        $order->save();
        return back()->with('success', 'Hasil ' . strtoupper($request->tipe) . ' berhasil disimpan!');
    }

    public function detail($id) { $order = Order::findOrFail($id); return view('order.detail', compact('order')); }

    public function printSize($id) { $order = Order::findOrFail($id); return view('order.print_size', compact('order')); }

    public function update(Request $request, $id) {
        $request->validate(['customer' => 'required', 'produk' => 'required', 'jumlah' => 'required|integer|min:1']);
        $order = Order::findOrFail($id);
        $order->update([
            'kode_order' => $request->kode_order, 'customer' => $request->customer, 'produk' => $request->produk,
            'size_s' => $request->size_s ?? 0, 'size_m' => $request->size_m ?? 0, 'size_l' => $request->size_l ?? 0, 'size_xl' => $request->size_xl ?? 0,
            'jumlah' => $request->jumlah, 'qty_order' => max(0, $request->jumlah - $order->qty_produksi),
        ]);
        return redirect('/order')->with('success', 'Data PO berhasil diperbarui!');
    }

    // --- PENGIRIMAN ---
    public function shipmentHistory() { $shipments = Shipment::with('order')->latest()->get(); return view('order.shipment_history', compact('shipments')); }
    public function suratJalan($id) { $shipment = Shipment::with('order')->findOrFail($id); return view('order.surat_jalan', compact('shipment')); }
    public function pengirimanCreate($id) { $order = Order::findOrFail($id); return view('order.pengiriman_create', compact('order')); }
    public function pengirimanStore(Request $request) {
        $request->validate(['order_id' => 'required|exists:orders,id']);
        $order = Order::findOrFail($request->order_id);
        $current_ship = ['s' => $request->ship_s ?? 0, 'm' => $request->ship_m ?? 0, 'l' => $request->ship_l ?? 0, 'xl' => $request->ship_xl ?? 0];
        $total = array_sum($current_ship);
        $shipment = Shipment::create(['order_id' => $order->id, 'no_surat_jalan' => 'SJ/' . date('Ymd') . '/' . strtoupper(uniqid()), 's' => $current_ship['s'], 'm' => $current_ship['m'], 'l' => $current_ship['l'], 'xl' => $current_ship['xl'], 'total' => $total]);
        $order->qty_terkirim += $total; $order->save();
        return redirect()->route('order.suratJalan', $shipment->id)->with('success', 'Pengiriman disimpan!');
    }

    // --- DIVISI ---
    public function index() { return view('order.index', ['orders' => Order::latest()->get()]); }
    public function create() { return view('order.create'); }
    public function store(Request $r) {
        $r->validate(['customer' => 'required', 'produk' => 'required', 'jumlah' => 'required|integer|min:1']);
        Order::create(['kode_order' => 'ORD-' . time(), 'customer' => $r->customer, 'produk' => $r->produk, 'jumlah' => $r->jumlah, 'qty_order' => $r->jumlah, 'qty_cutting' => 0, 'qty_produksi' => 0, 'qty_finishing' => 0, 'qty_qc' => 0, 'qty_reject' => 0, 'qty_terkirim' => 0, 'progress' => 0, 'status' => 'order']);
        return redirect('/order')->with('success', 'Order dibuat!');
    }
    public function edit($id) { $order = Order::findOrFail($id); return view('order.edit', compact('order')); }
    public function cutting() { return view('order.cutting', ['orders' => Order::where('status', '!=', 'selesai')->latest()->get()]); }
    public function sewing() { return view('order.sewing', ['orders' => Order::whereNotIn('status', ['selesai'])->latest()->get()]); }
    public function finishing() { return view('order.finishing', ['orders' => Order::where('qty_produksi', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); }
    public function qc() { return view('order.qc', ['orders' => Order::where('qty_finishing', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); }
    public function report() { return view('order.report', ['orders' => Order::latest()->get()]); }

    // Tambahkan di bagian atas file:


// Tambahkan fungsinya:
public function inventoryIndex() {
    $materials = Material::all();
    return view('inventory.index', compact('materials'));
}

public function procurementIndex() {
    $purchases = Purchasing::with('material')->latest()->get();
    $materials = Material::all(); // Buat pilihan bahan saat beli
    return view('procurement.index', compact('purchases', 'materials'));
}
}