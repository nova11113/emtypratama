<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Material;
use App\Models\MaterialLog; // Tambahkan ini
use App\Models\Purchasing; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Tambahkan ini wajib buat query DB::table

class OrderController extends Controller
{
    
/**
     * Dashboard - Statistik Real-Time
     */
    /**
     * Dashboard - Statistik Real-Time & Notifikasi Chat
     */
    public function dashboard(\Illuminate\Http\Request $request) {
    // 1. Ambil tanggal dari input 'tanggal'. Kalau kosong, pake hari ini.
    $tanggal_pilihan = $request->get('tanggal', now()->toDateString()); 
    
    $orders = Order::latest()->get();
    
    // 2. Tarik Log berdasarkan TANGGAL PILIHAN (Bukan cuma hari ini)
    $dailyReports = \App\Models\ProductionLog::with('order')
                    ->whereDate('tanggal', $tanggal_pilihan)
                    ->latest()
                    ->get();

    // --- TAMBAHKAN BARIS INI ---
    $total_setoran_harian = $dailyReports->sum('qty'); 
    // ---------------------------

    // 3. Hitung statistik (Data global tetep pake $orders)
    $total_terkirim = $orders->sum('qty_terkirim') ?? 0;
    $total_order   = ($orders->sum('jumlah') ?? 0) - $total_terkirim;
    $total_qc      = $orders->sum('qty_qc') ?? 0;
    $total_gudang  = $total_qc - $total_terkirim;

    $notif_chat = \App\Models\InternalRequest::where('is_read', false)->count();
    $pesan_terbaru = \App\Models\InternalRequest::where('is_read', false)->latest()->take(5)->get();

    return view('dashboard', [
        'orders'        => $orders,
        'total_order'   => $total_order,
        'total_cutting' => $orders->sum('qty_cutting'),
        'total_sewing'  => $orders->sum('qty_produksi'),
        'total_qc'      => $total_qc,
        'total_reject'  => $orders->sum('qty_reject'),
        'total_gudang'  => $total_gudang,
        'notif_chat'    => $notif_chat,
        'pesan_terbaru' => $pesan_terbaru,
        'dailyReports'  => $dailyReports,
        'tanggal'       => $tanggal_pilihan // Kirim balik biar kalendernya nggak reset
    ]);

}

    public function kirimRequest(Request $request) {
        \App\Models\InternalRequest::create([
            'divisi' => $request->divisi,
            'pesan' => $request->pesan
        ]);
        return back()->with('success', 'Pesan bantuan berhasil dikirim!');
    }
    public function bacaRequest($id) {
    $request = \App\Models\InternalRequest::findOrFail($id);
    $request->update(['is_read' => true]);

    return back()->with('success', 'Pesan ditandai sebagai sudah dibaca!');
}

public function riwayatRequest() {
    // Ambil semua pesan, urutkan dari yang paling baru
    $semua_pesan = \App\Models\InternalRequest::latest()->paginate(20);

    return view('internal.riwayat', compact('semua_pesan'));
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
        'satuan'      => $request->satuan, // Ambil dari input hidden/readonly tadi
        'status' => 'pending' // Default pending biar gak langsung nambah stok
    ]);

    return back()->with('success', 'Pesanan pembelian berhasil dicatat!');
}
/**
     * INVENTORY - Buat Master Bahan Baru
     */
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
     * INVENTORY - Update Stok Manual & Catat Log Mutasi
     */
    public function inventoryUpdate(Request $request, $id) {
    $request->validate([
        'stok_baru' => 'required|numeric',
        'keterangan' => 'nullable|string'
    ]);

    $material = Material::findOrFail($id);
    $stok_lama = $material->stok;
    $selisih = $request->stok_baru - $stok_lama;
    
    $material->stok = $request->stok_baru;
    $material->save();

    // TAMBAHKAN BACKSLASH (\) di depan App supaya Laravel nggak bingung nyari modelnya
    \App\Models\MaterialLog::create([
        'material_id' => $id,
        'tipe' => ($selisih >= 0) ? 'masuk' : 'keluar',
        'jumlah' => abs($selisih),
        'keterangan' => $request->keterangan ?? "Penyesuaian stok manual (Stok lama: $stok_lama)"
    ]);

    return back()->with('success', "Stok {$material->nama_bahan} berhasil diupdate!");
}

    /**
     * INVENTORY - Tampilkan Riwayat Bahan
     */
    public function inventoryHistory($id) {
        $material = Material::findOrFail($id);
        
        // Memastikan variabel bernama $logs agar dibaca oleh Blade
        $logs = MaterialLog::where('material_id', $id)
                    ->latest()
                    ->paginate(20);

        return view('inventory.history', compact('material', 'logs'));
    }

    /**
     * PROCUREMENT - Terima Barang & Tambah Stok Otomatis
     */
    public function terimaBahan($id) {
        $pembelian = Purchasing::findOrFail($id);
        
        if($pembelian->status == 'diterima') {
            return back()->with('error', 'Barang sudah diterima sebelumnya.');
        }

        // 1. Update status pembelian
        $pembelian->update(['status' => 'diterima']);
        
        // 2. Tambah stok di Master Bahan
        $material = Material::findOrFail($pembelian->material_id);
        $material->increment('stok', $pembelian->jumlah);

        // 3. CATAT KE LOG MUTASI
        MaterialLog::create([
            'material_id' => $pembelian->material_id,
            'tipe' => 'masuk',
            'jumlah' => $pembelian->jumlah,
            'keterangan' => "Penerimaan PO dari: " . $pembelian->supplier
        ]);

        return back()->with('success', 'Stok bahan berhasil ditambahkan dan dicatat!');
    }

    /**
     * PRODUKSI - Gunakan Bahan & Potong Stok
     */
    public function gunakanBahan(Request $r) {
        $r->validate([
            'material_id' => 'required',
            'jumlah' => 'required|numeric',
        ]);

        $material = Material::findOrFail($r->material_id);
        
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
            'keterangan' => 'Pemakaian bahan untuk Order ID: ' . ($r->order_id ?? 'Tanpa ID')
        ]);

        return back()->with('success', 'Bahan berhasil dipotong dari stok!');
    }
    public function updateProgressBulk(Request $request) {
    // 1. Validasi Input
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'qty_tambah' => 'required|integer|min:1',
        'tipe' => 'required|in:cutting,produksi,finishing,qc',
        'size_type' => 'required|in:s,m,l,xl'
    ]);

    $order = Order::findOrFail($request->order_id);
    $size = $request->size_type;
    $qty = $request->qty_tambah;

    // --- MULAI PENGECEKAN TIAP DIVISI ---

    if ($request->tipe == 'cutting') {
        $target = 'size_' . $size; $current = 'cutting_' . $size;
        if (($order->$current + $qty) > $order->$target) { 
            return back()->with('error', "Gagal! Melebihi target Size " . strtoupper($size)); 
        }

        // Potong stok kain jika ada
        if ($request->material_id && $request->jumlah_bahan > 0) {
            $material = Material::findOrFail($request->material_id);
            if ($material->stok < $request->jumlah_bahan) { return back()->with('error', "Stok kain tidak cukup!"); }
            $material->decrement('stok', $request->jumlah_bahan);
            \App\Models\MaterialLog::create([
                'material_id' => $material->id, 'tipe' => 'keluar', 'jumlah' => $request->jumlah_bahan,
                'keterangan' => "Potong kain untuk Order: " . $order->kode_order
            ]);
        }
        $order->$current += $qty; 
        $order->qty_cutting += $qty;

    } elseif ($request->tipe == 'produksi') { // SEWING
        $target_cutting = 'cutting_' . $size; $target_order = 'size_' . $size; $current = 'sewing_' . $size;

        if (!$order->$target_cutting || $order->$target_cutting <= 0) {
            return back()->with('error', "Gagal! Hasil potongan belum ada.");
        }
        if (($order->$current + $qty) > $order->$target_cutting) { 
            return back()->with('error', "Gagal! Melebihi hasil potong."); 
        }
        $order->$current += $qty; 
        $order->qty_produksi += $qty;
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

    // --- JIKA LOLOS VALIDASI, BARU CATAT KE LOG HARIAN ---
    \App\Models\ProductionLog::create([
        'order_id' => $order->id,
        'divisi'   => $request->tipe,
        'size'     => $size,
        'qty'      => $qty,
        'tanggal'  => now()->toDateString(),
    ]);

    // Update Progress & Status
    if ($order->jumlah > 0) { $order->progress = min(100, ($order->qty_qc / $order->jumlah) * 100); }
    if ($order->progress >= 100) $order->status = 'selesai';
    elseif ($order->qty_qc > 0) $order->status = 'qc';
    elseif ($order->qty_finishing > 0) $order->status = 'finishing';
    elseif ($order->qty_produksi > 0) $order->status = 'produksi';
    elseif ($order->qty_cutting > 0) $order->status = 'cutting';

    $order->save();
    return back()->with('success', 'Data berhasil disimpan ke sistem dan laporan harian!');
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
    
    // 1. Ambil data kirim dari input
    $s  = $request->ship_s ?? 0;
    $m  = $request->ship_m ?? 0;
    $l  = $request->ship_l ?? 0;
    $xl = $request->ship_xl ?? 0;
    $total = $s + $m + $l + $xl;

    // 2. Simpan ke tabel Shipment (Riwayat Pengiriman)
    $shipment = Shipment::create([
        'order_id' => $order->id,
        'no_surat_jalan' => 'SJ/' . date('Ymd') . '/' . strtoupper(uniqid()),
        's' => $s, 'm' => $m, 'l' => $l, 'xl' => $xl, 
        'total' => $total
    ]);

    // 3. UPDATE TABEL ORDERS (Ini kuncinya bro!)
    // Update kolom kirim per size supaya stok di gudang berkurang
    $order->ship_s += $s;
    $order->ship_m += $m;
    $order->ship_l += $l;
    $order->ship_xl += $xl;
    
    // Update total gelondongan
    $order->qty_terkirim += $total; 
    
    $order->save();

    // 4. Return JSON supaya AJAX di Blade lu nggak error "Kesalahan Sistem"
    return response()->json([
        'success' => true,
        'shipment_id' => $shipment->id
    ]);
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
    public function cutting() {
    $orders = Order::where('status', '!=', 'selesai')->latest()->get();
    $materials = Material::all(); // <--- Ambil semua data kain dari gudang
    
    return view('order.cutting', compact('orders', 'materials'));
}
    public function sewing() { return view('order.sewing', ['orders' => Order::whereNotIn('status', ['selesai'])->latest()->get()]); }
    public function finishing() { return view('order.finishing', ['orders' => Order::where('qty_produksi', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); }
    public function qc() { return view('order.qc', ['orders' => Order::where('qty_finishing', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); }
    public function report(Request $request) {
    $tanggal = $request->get('tanggal', date('Y-m-d'));
    $periode = $request->get('periode', 'harian');

    // 1. Ambil list PO untuk tabel
    $orders = Order::latest()->get(); 

    // 2. AMBIL DATA REAL HARI INI DARI LOG (Ini kuncinya!)
    $rekap = [
        'sewing'    => \App\Models\ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'produksi')->sum('qty'),
        'finishing' => \App\Models\ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'finishing')->sum('qty'),
        'qc'        => \App\Models\ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'qc')->sum('qty'),
        'sisa'      => $orders->sum('qty_order') // Sisa target emang biasanya total PO
    ];

    return view('order.report', compact('orders', 'rekap', 'tanggal'));
}

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