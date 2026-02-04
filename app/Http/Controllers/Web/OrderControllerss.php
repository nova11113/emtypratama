<?php

use App\Http\Controllers\Web\OrderController;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionLog; // WAJIB ADA
use App\Models\InternalRequest; // WAJIB ADA
use App\Models\Shipment;
use App\Models\Material;
use App\Models\MaterialLog; 
use App\Models\Purchasing; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * DASHBOARD - Statistik Real-Time
     */
    public function dashboard() {
    return "SAYONARA ERROR! KODE INI JALAN!";
    $today = now()->toDateString(); 
    $orders = Order::latest()->get();
    
    // 1. TARIK DATA LOG PRODUKSI HARIAN (Penting!)
    $dailyReports = \App\Models\ProductionLog::with('order')
                    ->where('tanggal', $today)
                    ->latest()
                    ->get();

    $total_terkirim = $orders->sum('qty_terkirim') ?? 0;
    $total_order   = ($orders->sum('jumlah') ?? 0) - $total_terkirim;
    $total_qc      = $orders->sum('qty_qc') ?? 0;
    $total_gudang  = $total_qc - $total_terkirim;

    $notif_chat = \App\Models\InternalRequest::where('is_read', false)->count();
    $pesan_terbaru = \App\Models\InternalRequest::where('is_read', false)->latest()->take(5)->get();

    // 2. PASTIKAN SEMUA VARIABEL MASUK KE ARRAY RETURN
    return view('dashboard', [
        'orders' => $orders,
        'total_order' => $total_order,
        'total_cutting' => $orders->sum('qty_cutting'),
        'total_sewing' => $orders->sum('qty_produksi'),
        'total_qc' => $total_qc,
        'total_reject' => $orders->sum('qty_reject'),
        'total_gudang' => $total_gudang,
        'notif_chat' => $notif_chat,
        'pesan_terbaru' => $pesan_terbaru,
        
        // VARIABEL SAKTI YANG TADI KETINGGALAN:
        'dailyReports' => $dailyReports 
    ]);
}
    

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
     * FIX: Menggunakan material_id agar sinkron dengan DB
     */
    public function inventoryUpdate(Request $request, $id) {
        $request->validate([
            'stok_baru' => 'required|numeric',
            'keterangan' => 'nullable|string'
        ]);

        $material = Material::findOrFail($id);
        $stok_lama = $material->stok;
        $selisih = $request->stok_baru - $stok_lama;
        $aksi = ($selisih >= 0) ? 'masuk' : 'keluar';

        $material->stok = $request->stok_baru;
        $material->save();

        MaterialLog::create([
            'material_id' => $material->id, // Kolom DB lu adalah material_id
            'tipe' => $aksi,               // Kolom DB lu adalah tipe
            'jumlah' => abs($selisih),
            'keterangan' => $request->keterangan ?? "Update stok manual oleh Admin"
        ]);

        return back()->with('success', "Stok {$material->nama_bahan} berhasil diupdate!");
    }

    public function inventoryHistory($id) {
    $material = Material::findOrFail($id);
    
    // Tarik data dari piring yang bener (MaterialLog)
    $logs = MaterialLog::where('material_id', $id)
                ->latest()
                ->paginate(25);

    // Kirim ke meja (View)
    return view('inventory.history', compact('material', 'logs'));

    }

    public function procurementIndex() {
        $purchases = Purchasing::with('material')->latest()->get();
        $materials = Material::all();
        return view('procurement.index', compact('purchases', 'materials'));
    }

    public function procurementStore(Request $request) {
        $request->validate(['material_id' => 'required', 'supplier' => 'required', 'jumlah' => 'required|numeric']);

        Purchasing::create([
            'material_id' => $request->material_id,
            'supplier' => $request->supplier,
            'jumlah' => $request->jumlah,
            'satuan' => $request->satuan,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Pesanan pembelian berhasil dicatat!');
    }

    /**
     * FIX: Menggunakan material_id saat terima bahan
     */
    public function terimaBahan($id) {
        $pembelian = Purchasing::findOrFail($id);
        if($pembelian->status == 'diterima') return back()->with('error', 'Barang sudah diterima.');

        $pembelian->update(['status' => 'diterima']);
        $material = Material::findOrFail($pembelian->material_id);
        $material->increment('stok', $pembelian->jumlah);

        MaterialLog::create([
            'material_id' => $material->id,
            'tipe' => 'masuk',
            'jumlah' => $pembelian->jumlah,
            'keterangan' => 'Penerimaan PO Pembelian #' . ($pembelian->no_po ?? $pembelian->id)
        ]);

        return back()->with('success', 'Stok bahan masuk dan tercatat!');
    }

    // --- FUNGSI PRODUKSI ---
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

    // --- 1. UPDATE STOK UTAMA (Tabel Orders) ---
    if ($request->tipe == 'cutting') {
        $target = 'size_' . $size; $current = 'cutting_' . $size;
        if (($order->$current + $qty) > $order->$target) return back()->with('error', "Melebihi target!");
        $order->$current += $qty; $order->qty_cutting += $qty;

    } elseif ($request->tipe == 'produksi') { // SEWING
        $target = 'cutting_' . $size; $current = 'sewing_' . $size;
        if (($order->$current + $qty) > $order->$target) return back()->with('error', "Kain potong tidak cukup!");
        $order->$current += $qty; $order->qty_produksi += $qty;

    } elseif ($request->tipe == 'finishing') {
        $target = 'sewing_' . $size; $current = 'finishing_' . $size;
        if (($order->$current + $qty) > $order->$target) return back()->with('error', "Hasil sewing tidak cukup!");
        $order->$current += $qty; $order->qty_finishing += $qty;

    } elseif ($request->tipe == 'qc') {
        $target = 'finishing_' . $size; $current = 'qc_' . $size;
        if (($order->$current + $qty) > $order->$target) return back()->with('error', "Hasil finishing tidak cukup!");
        $order->$current += $qty; $order->qty_qc += $qty;
    }

    // --- 2. CATAT KE LOG HARIAN (Biar Dashboard Muncul) ---
    \App\Models\ProductionLog::create([
        'order_id' => $order->id,
        'divisi'   => $request->tipe,
        'size'     => $size,
        'qty'      => $qty,
        'tanggal'  => now()->toDateString(),
    ]);

    // Update Progress Persentase
    if ($order->jumlah > 0) {
        $order->progress = min(100, ($order->qty_qc / $order->jumlah) * 100);
    }

    $order->save();
    return back()->with('success', 'Hasil ' . strtoupper($request->tipe) . ' berhasil disimpan dan tercatat di dashboard!');
}
}