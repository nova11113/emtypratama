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
     * UPDATE PROGRESS PRODUKSI
     * Mengurangi qty_order (Sisa Target) secara otomatis saat QC Pass.
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

            // 1. Mapping Tahap
            if ($tipe == 'cutting') {
                $currPrefix = 'cutting_';
                $colPrev    = $size; 
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
                $isReject   = ($request->is_reject == 'ya');
                $currPrefix = $isReject ? 'qc_reject_' : 'qc_';
                $colPrev    = 'finishing_' . $size;
                $displayPrev = "HASIL FINISHING " . strtoupper($size);
            }

            $colCurr = $currPrefix . $size;

            // 2. Cek Stok Tahap Sebelumnya
            $stokTersedia = (int) ($variant->$colPrev ?? 0);
            if ($stokTersedia < $qty) {
                return back()->with('error', "Gagal! Stok di $displayPrev cuma sisa $stokTersedia pcs.");
            }

            // 3. Update Tabel Variant
            $variant->$colPrev -= $qty;
            $variant->$colCurr = ($variant->$colCurr ?? 0) + $qty;
            $variant->save();

            // 4. Update Tabel Order (Utama)
            $order->$colCurr = ($order->$colCurr ?? 0) + $qty;
            if ($tipe != 'cutting') {
                $order->$colPrev = max(0, ($order->$colPrev ?? 0) - $qty);
            }

            // Update Total Global & Sisa Target
            if ($tipe == 'qc' && isset($isReject) && $isReject) {
                $order->qty_reject = ($order->qty_reject ?? 0) + $qty;
            } else {
                $kolomTotal = ($tipe == 'produksi') ? 'qty_produksi' : 'qty_' . $tipe;
                $order->$kolomTotal = ($order->$kolomTotal ?? 0) + $qty;

                // KURANGI SISA TARGET SAAT QC PASS
                if ($tipe == 'qc' && !$isReject) {
                    $order->qty_order = max(0, ($order->qty_order ?? 0) - $qty);
                }
            }

            // 5. Log Produksi & History
            ProductionLog::create([
                'order_id' => $order->id, 'divisi' => $tipe, 'size' => $size, 'qty' => $qty, 'tanggal' => now()->toDateString()
            ]);

            if ($order->jumlah > 0) $order->progress = min(100, (($order->qty_qc ?? 0) / $order->jumlah) * 100);
            if ($order->progress >= 100) $order->status = 'selesai';
            
            $order->save();

            DB::commit();
            return back()->with('success', "✅ Berhasil! Data pengerjaan tersimpan.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    /**
     * LAPORAN PRODUKSI TERPADU
     * Mengambil Ringkasan, List Aktivitas Harian, dan Status PO.
     */
    public function report(Request $request) 
    {
        $tanggal = $request->get('tanggal', date('Y-m-d'));
        $periode = $request->get('periode', 'harian');

        // 1. QUERY LIST PENGERJAAN (LAPORAN HARIAN/BULANAN)
        $logQuery = ProductionLog::with('order'); 
        if ($periode == 'bulanan') {
            $logQuery->whereMonth('tanggal', date('m', strtotime($tanggal)))
                     ->whereYear('tanggal', date('Y', strtotime($tanggal)));
        } else {
            $logQuery->whereDate('tanggal', $tanggal);
        }
        $logs = $logQuery->latest()->get();

        // 2. QUERY RINGKASAN (STAT-BOX)
        $rekap = [
            'sewing'    => (clone $logQuery)->where('divisi', 'produksi')->sum('qty'),
            'finishing' => (clone $logQuery)->where('divisi', 'finishing')->sum('qty'),
            'qc'        => (clone $logQuery)->where('divisi', 'qc')->sum('qty'),
            'sisa'      => Order::sum('qty_order')
        ];

        // 3. QUERY DAFTAR SEMUA PO (MASTER PO)
        $orders = Order::where('status', '!=', 'selesai')->latest()->get();

        return view('order.report', compact('orders', 'rekap', 'logs', 'tanggal', 'periode'));
    }

    // --- VIEW METHODS ---
    public function cutting() { 
        return view('order.cutting', ['orders' => Order::where('status', '!=', 'selesai')->latest()->get(), 'materials' => Material::all()]); 
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
    public function printSize($id) { 
        $order = Order::with('variants')->findOrFail($id); 
        return view('production.print_size', compact('order')); 
    }
}
