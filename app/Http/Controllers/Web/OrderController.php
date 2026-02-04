<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderVariant;
use App\Models\Material;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index() {
        return view('order.index', ['orders' => Order::latest()->get()]);
    }

    public function create() {
        return view('order.create'); 
    }

    public function detail($id) {
        return view('order.detail', ['order' => Order::findOrFail($id)]);
    }

    public function store(Request $r) {
        $r->validate([
            'customer' => 'required', 
            'produk' => 'required', 
            'jumlah' => 'required|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $imageName = null;
        if ($r->hasFile('image')) {
            $file = $r->file('image');
            $imageName = time() . '_' . str_replace(' ', '_', $r->produk) . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/orders');
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            $file->move($path, $imageName);
        }

        Order::create([
            'kode_order' => 'ORD-' . time(),
            'customer'   => $r->customer,
            'produk'     => $r->produk,
            'image'      => $imageName,
            'jumlah'     => $r->jumlah,
            'qty_order'  => $r->jumlah,
            'status'     => 'order'
        ]);

        return redirect()->route('order.index')->with('success', 'PO Baru Berhasil Disimpan!');
    }

    public function updateVariants(Request $r, $id)
    {
        $order = Order::findOrFail($id);
        
        // Menggunakan updateOrCreate agar progress produksi (cutting, sewing, dll) TIDAK TERHAPUS
        foreach($r->warna as $key => $warna) {
            if(!empty($warna)) {
                $s = $r->s[$key] ?? 0;
                $m = $r->m[$key] ?? 0;
                $l = $r->l[$key] ?? 0;
                $xl = $r->xl[$key] ?? 0;

                $order->variants()->updateOrCreate(
                    ['warna' => $warna], // Kunci pencarian
                    [
                        's' => $s,
                        'm' => $m,
                        'l' => $l,
                        'xl' => $xl,
                        'total' => ($s + $m + $l + $xl)
                    ]
                );
            }
        }

        return back()->with('success', 'Rincian warna diperbarui (Progress produksi tetap aman).');
    }

    public function bulk(Request $request)
{
    $request->validate([
        'order_id'   => 'required|exists:orders,id',
        'variant_id' => 'required|exists:order_variants,id',
        'tipe'       => 'required', // cutting, produksi, atau finishing
        'size_type'  => 'required|in:s,m,l,xl',
        'qty_tambah' => 'required|numeric|min:1',
    ]);

    try {
        DB::beginTransaction();

        $order   = Order::findOrFail($request->order_id);
        $variant = OrderVariant::findOrFail($request->variant_id);
        
        $size      = strtolower($request->size_type);
        $qty       = (int) $request->qty_tambah;
        $tipe      = $request->tipe; 

        // Mapping nama kolom: produksi -> sewing
        $kolomFix  = ($tipe == 'produksi' || $tipe == 'sewing') ? 'sewing' : $tipe;
        $namaKolom = $kolomFix . '_' . $size; 

        // 1. UPDATE TABEL VARIAN (Detail Warna) - BYPASS VALIDASI
        if (Schema::hasColumn('order_variants', $namaKolom)) {
            $variant->$namaKolom = ($variant->$namaKolom ?? 0) + $qty;
            $variant->save();
        }

        // 2. UPDATE TABEL UTAMA (Rekap Total) - BYPASS VALIDASI
        if (Schema::hasColumn('orders', $namaKolom)) {
            $order->$namaKolom = ($order->$namaKolom ?? 0) + $qty;
        }

        // 3. Update Akumulasi qty & Status Otomatis
        if ($kolomFix == 'cutting') {
            $order->qty_cutting = ($order->qty_cutting ?? 0) + $qty;
            if ($request->filled('material_id')) {
                $material = Material::find($request->material_id);
                if ($material) {
                    $material->decrement('stok', $request->jumlah_bahan);
                }
            }
        } elseif ($kolomFix == 'sewing') {
            $order->qty_produksi = ($order->qty_produksi ?? 0) + $qty;
            // Update Sisa Order
            $order->qty_order = max(0, $order->jumlah - $order->qty_produksi);
        } elseif ($kolomFix == 'finishing') {
            $order->qty_finishing = ($order->qty_finishing ?? 0) + $qty;
        }

        // 4. CATAT KE PRODUCTION LOG (Biar Laporan Tetap Jalan)
        \App\Models\ProductionLog::create([
            'order_id' => $order->id,
            'divisi'   => $tipe,
            'size'     => $size,
            'qty'      => $qty,
            'tanggal'  => now()->toDateString()
        ]);

        // 5. Update Progress & Status
        if ($order->jumlah > 0) {
            $order->progress = min(100, (($order->qty_finishing ?? 0) / $order->jumlah) * 100);
        }

        $order->save();

        DB::commit();
        return back()->with('success', "✅ BERHASIL! Input $qty pcs $kolomFix warna $variant->warna tembus ke database.");

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', '❌ Gagal simpan: ' . $e->getMessage());
    }
}

    public function show($id) {
        $order = Order::with('variants')->findOrFail($id);
        return view('order.detail', compact('order'));
    }

    public function edit($id) {
        $order = Order::findOrFail($id);
        return view('order.edit', compact('order'));
    }

    public function update(Request $r, $id) {
        $order = Order::findOrFail($id);
        $order->update([
            'size_s' => $r->size_s ?? 0,
            'size_m' => $r->size_m ?? 0,
            'size_l' => $r->size_l ?? 0,
            'size_xl' => $r->size_xl ?? 0,
        ]);
        return redirect()->route('order.index')->with('success', 'Rincian Size Berhasil Diperbarui!');
    }

    public function updateChart(Request $r, $id) {
        $order = Order::findOrFail($id);
        if ($r->hasFile('size_chart')) {
            $file = $r->file('size_chart');
            $name = 'chart_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/charts'), $name);
            $order->update(['size_chart' => $name]);
        }
        return back()->with('success', 'Size Chart Berhasil Diupload!');
    }

    public function pengirimanStore(Request $request) {
        $order = Order::findOrFail($request->order_id);
        $total = ($request->ship_s ?? 0) + ($request->ship_m ?? 0) + ($request->ship_l ?? 0) + ($request->ship_xl ?? 0);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'no_surat_jalan' => 'SJ/' . date('Ymd') . '/' . strtoupper(uniqid()),
            'total' => $total,
        ]);
        $order->increment('qty_terkirim', $total);
        return response()->json(['success' => true, 'shipment_id' => $shipment->id]);
    }
}