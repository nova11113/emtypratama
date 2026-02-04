<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Material;
use App\Models\Purchasing;
use App\Models\MaterialLog; // Pastikan model log ini ada jika ingin mencatat riwayat pemakaian
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

    /**
     * PROCUREMENT (Pembelian Bahan)
     */
    public function procurementIndex() {
        $purchases = Purchasing::with('material')->latest()->get();
        $materials = Material::all(); 
        return view('procurement.index', compact('purchases', 'materials'));
    }

    public function procurementStore(Request $request) {
        $request->validate([
            'material_id' => 'required',
            'supplier' => 'required',
            'jumlah' => 'required|numeric',
            'satuan' => 'required' // Menangkap satuan dari modal (Roll/Kg/Pcs)
        ]);

        Purchasing::create([
            'material_id' => $request->material_id,
            'supplier' => $request->supplier,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan, // Pastikan ini tersimpan sesuai inputan
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pesanan pembelian berhasil dicatat!');
    }

    public function terimaBahan($id) {
        $pembelian = Purchasing::findOrFail($id);
        
        if($pembelian->status == 'diterima') {
            return back()->with('error', 'Barang ini sudah pernah diterima.');
        }

        $pembelian->update(['status' => 'diterima']);

        // Nambah stok di Master Bahan
        $material = Material::find($pembelian->material_id);
        $material->increment('stok', $pembelian->jumlah);

        return back()->with('success', 'Stok bahan berhasil ditambahkan!');
    }

    /**
     * INVENTORY (Master Stok)
     */
    public function inventoryIndex() {
        $materials = Material::all();
        return view('inventory.index', compact('materials'));
    }

    public function inventoryStore(Request $request) {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'satuan' => 'required',
            'minimal_stok' => 'required|numeric'
        ]);

        Material::create([
            'nama_bahan' => $request->nama_bahan,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'stok' => 0, 
            'minimal_stok' => $request->minimal_stok,
        ]);

        return back()->with('success', 'Master bahan baku berhasil dibuat!');
    }

    /**
     * PRODUKSI (Alur Cutting -> Sewing -> QC)
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

        // --- Logika Pergerakan Barang Antar Divisi ---
        if ($request->tipe == 'cutting') {
            $target = 'size_' . $size; $current = 'cutting_' . $size;
            if (($order->$current + $qty) > $order->$target) return back()->with('error', "Gagal! Melebihi target Size " . strtoupper($size));
            $order->$current += $qty; $order->qty_cutting += $qty;
            
        } elseif ($request->tipe == 'produksi') {
            $target = 'cutting_' . $size; $current = 'sewing_' . $size;
            if (($order->$current + $qty) > $order->$target) return back()->with('error', "Gagal! Kain hasil cutting tidak cukup.");
            $order->$current += $qty; $order->qty_produksi += $qty;
            $order->qty_order = max(0, $order->jumlah - $order->qty_produksi);

        } elseif ($request->tipe == 'finishing') {
            $target = 'sewing_' . $size; $current = 'finishing_' . $size;
            if (($order->$current + $qty) > $order->$target) return back()->with('error', "Gagal! Stok jahit tidak cukup.");
            $order->$current += $qty; $order->qty_finishing += $qty;

        } elseif ($request->tipe == 'qc') {
            $target = 'finishing_' . $size; $colQC = 'qc_' . $size; $colReject = 'qc_reject_' . $size;
            if ((($order->$colQC + $order->$colReject) + $qty) > $order->$target) return back()->with('error', "Gagal! Sisa finishing tidak cukup.");
            if ($request->is_reject == 'ya') { $order->$colReject += $qty; $order->qty_reject += $qty; }
            else { $order->$colQC += $qty; $order->qty_qc += $qty; }
        }

        // --- Update Status Global ---
        if ($order->jumlah > 0) $order->progress = min(100, ($order->qty_qc / $order->jumlah) * 100);
        
        $order->status = match(true) {
            $order->progress >= 100 => 'selesai',
            $order->qty_qc > 0 => 'qc',
            $order->qty_finishing > 0 => 'finishing',
            $order->qty_produksi > 0 => 'produksi',
            $order->qty_cutting > 0 => 'cutting',
            default => 'order'
        };

        $order->save();
        return back()->with('success', 'Hasil ' . strtoupper($request->tipe) . ' berhasil disimpan!');
    }

    // --- Sisanya Tetap Sama (Pengiriman, Index, Create, dll) ---
    public function index() { return view('order.index', ['orders' => Order::latest()->get()]); }
    public function create() { return view('order.create'); }
    public function store(Request $r) {
        $r->validate(['customer' => 'required', 'produk' => 'required', 'jumlah' => 'required|integer|min:1']);
        Order::create(['kode_order' => 'ORD-' . time(), 'customer' => $r->customer, 'produk' => $r->produk, 'jumlah' => $r->jumlah, 'qty_order' => $r->jumlah, 'status' => 'order', 'progress' => 0]);
        return redirect('/order')->with('success', 'Order dibuat!');
    }
}