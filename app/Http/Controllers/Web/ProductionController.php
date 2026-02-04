<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVariant;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\ProductionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    /**
     * Update Progress Produksi (Cutting, Sewing, Finishing, QC)
     * Logika ini sudah bypass validasi ketat agar fleksibel di lapangan.
     */
    public function updateProgress(Request $request) 
{
    $request->validate([
        'order_id'   => 'required|exists:orders,id',
        'variant_id' => 'required|exists:order_variants,id',
        'qty_tambah' => 'required|integer|min:1',
        'tipe'       => 'required|in:cutting,produksi,finishing,qc',
        'size_type'  => 'required|in:s,m,l,xl'
    ]);

    try {
        DB::beginTransaction();

        $order   = Order::findOrFail($request->order_id);
        $variant = OrderVariant::findOrFail($request->variant_id);
        $size    = $request->size_type;
        $qty     = (int) $request->qty_tambah;
        $tipe    = $request->tipe;

        // --- 1. MAPPING TAHAP (SEKARANG VS SEBELUMNYA) ---
        if ($tipe == 'cutting') {
            $currPrefix = 'cutting_';
            $colPrev    = $size; // KHUSUS CUTTING: Ambil dari target awal (kolom s, m, l, xl)
            $displayPrev = "TARGET SIZE " . strtoupper($size);
        } elseif ($tipe == 'produksi') {
            $currPrefix = 'sewing_';
            $colPrev    = 'cutting_' . $size;
            $displayPrev = "HASIL POTONG " . strtoupper($size);
        } elseif ($tipe == 'finishing') {
            $currPrefix = 'finishing_';
            $colPrev    = 'sewing_' . $size;
            $displayPrev = "HASIL JAHIT " . strtoupper($size);
        } else {
            // Logika QC (Lolos vs Rijek)
            $isReject   = ($request->is_reject == 'ya');
            $currPrefix = $isReject ? 'qc_reject_' : 'qc_';
            $colPrev    = 'finishing_' . $size;
            $displayPrev = "HASIL FINISHING " . strtoupper($size);
        }

        $colCurr = $currPrefix . $size;

        // --- 2. CEK STOK DI TAHAP SEBELUMNYA (TABEL VARIAN) ---
        $stokTersedia = (int) ($variant->$colPrev ?? 0);
        if ($stokTersedia < $qty) {
            return back()->with('error', "Gagal! Stok di $displayPrev cuma sisa $stokTersedia pcs.");
        }

        // --- 3. EKSEKUSI PINDAH STOK (TABEL VARIAN - DETAIL WARNA) ---
        $variant->$colPrev -= $qty;
        $variant->$colCurr = ($variant->$colCurr ?? 0) + $qty;
        $variant->save();

        // --- 4. UPDATE REKAP DI TABEL ORDER (TABEL UTAMA) ---
        // PENTING: Update kolom hasil (e.g. cutting_s) di tabel orders
        $order->$colCurr = ($order->$colCurr ?? 0) + $qty;

        // HANYA kurangi stok tahap sebelumnya di tabel orders jika kolomnya ADA (bukan cutting)
        if ($tipe != 'cutting') {
            $order->$colPrev = max(0, ($order->$colPrev ?? 0) - $qty);
        }

        // Update Akumulasi Total Global
        if ($tipe == 'qc' && isset($isReject) && $isReject) {
            $order->qty_reject = ($order->qty_reject ?? 0) + $qty;
        } else {
            $kolomTotal = ($tipe == 'produksi') ? 'qty_produksi' : 'qty_' . $tipe;
            $order->$kolomTotal = ($order->$kolomTotal ?? 0) + $qty;
        }

        // --- 5. LOGIKA KHUSUS CUTTING (POTONG KAIN) ---
        if ($tipe == 'cutting' && $request->filled('material_id')) {
            $material = Material::findOrFail($request->material_id);
            if ($material->stok < $request->jumlah_bahan) {
                throw new \Exception("Stok kain " . $material->nama_bahan . " tidak cukup!");
            }
            $material->decrement('stok', $request->jumlah_bahan);
            \App\Models\MaterialLog::create([
                'material_id' => $material->id, 'tipe' => 'keluar', 'jumlah' => $request->jumlah_bahan,
                'keterangan' => "Potong kain PO: $order->kode_order"
            ]);
        }

        // --- 6. CATAT HISTORY (PRODUCTION LOG) ---
        ProductionLog::create([
            'order_id' => $order->id, 'divisi' => $tipe, 'size' => $size, 'qty' => $qty, 'tanggal' => now()->toDateString()
        ]);

        // Update Progress & Status
        if ($order->jumlah > 0) $order->progress = min(100, (($order->qty_qc ?? 0) / $order->jumlah) * 100);
        
        // Auto Update Status PO
        if ($order->progress >= 100) $order->status = 'selesai';
        elseif (($order->qty_qc ?? 0) > 0) $order->status = 'qc';
        elseif (($order->qty_finishing ?? 0) > 0) $order->status = 'finishing';
        elseif (($order->qty_produksi ?? 0) > 0) $order->status = 'produksi';
        elseif (($order->qty_cutting ?? 0) > 0) $order->status = 'cutting';

        $order->save();

        DB::commit();
        return back()->with('success', "✅ Berhasil! $qty pcs dipindahkan ke divisi " . strtoupper($tipe));

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', '❌ Gagal: ' . $e->getMessage());
    }
}

    // --- VIEW METHODS ---
    public function cutting() { 
        return view('order.cutting', [
            'orders' => Order::where('status', '!=', 'selesai')->latest()->get(), 
            'materials' => Material::all()
        ]); 
    }
    public function sewing() { 
        return view('order.sewing', ['orders' => Order::where('status', '!=', 'selesai')->latest()->get()]); 
    }
    public function finishing() { 
        return view('order.finishing', ['orders' => Order::where('qty_produksi', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); 
    }
    public function qc() { 
        return view('order.qc', ['orders' => Order::where('qty_finishing', '>', 0)->where('status', '!=', 'selesai')->latest()->get()]); 
    }

    public function report(Request $request) {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        return view('order.report', [
            'orders' => Order::latest()->get(),
            'tanggal' => $tanggal,
            'rekap' => [
                'sewing' => ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'produksi')->sum('qty'),
                'finishing' => ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'finishing')->sum('qty'),
                'qc' => ProductionLog::whereDate('tanggal', $tanggal)->where('divisi', 'qc')->sum('qty'),
            ]
        ]);
    }

    public function printSize($id) {
        $order = Order::with('variants')->findOrFail($id);
        return view('production.print_size', compact('order'));
    }
}