<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Tampilan Gudang (Stok Siap Kirim)
     */
    public function index()
    {
        $orders = Order::where('qty_qc', '>', 0)->latest()->get();
        return view('order.gudang', compact('orders'));
    }

    /**
     * Riwayat Pengiriman / Surat Jalan
     */
    public function shipmentHistory()
    {
        $shipments = Shipment::with('order')->latest()->get();
        return view('order.shipment_history', compact('shipments'));
    }

    /**
     * Simpan Data Pengiriman & Buat Riwayat (AJAX Compatible)
     */
    public function store(Request $r)
    {
        // 1. Validasi input dari Modal
        $r->validate([
            'order_id' => 'required|exists:orders,id',
            'ekspedisi' => 'required'
        ]);

        $order = Order::findOrFail($r->order_id);
        
        // Hitung total qty kirim dari input per size
        $totalKirim = (int)$r->ship_s + (int)$r->ship_m + (int)$r->ship_l + (int)$r->ship_xl;

        // Cek kalau input kosong
        if ($totalKirim <= 0) {
            return response()->json([
                'success' => false, 
                'message' => 'Isi jumlah kirim minimal 1 pcs pada salah satu size bro!'
            ], 422);
        }

        // 2. Simpan ke tabel shipments (Record Surat Jalan)
        $shipment = Shipment::create([
            'order_id'       => $order->id,
            'no_surat_jalan' => 'SJ/' . date('Ymd') . '/' . strtoupper(uniqid()),
            's'              => (int)$r->ship_s,
            'm'              => (int)$r->ship_m,
            'l'              => (int)$r->ship_l,
            'xl'             => (int)$r->ship_xl,
            'total'          => $totalKirim,
            'ekspedisi'      => $r->ekspedisi,
        ]);

        // 3. Update Akumulasi di Tabel Orders (Biar stok gudang berkurang)
        $order->qty_terkirim += $totalKirim;
        $order->ship_s       += (int)$r->ship_s;
        $order->ship_m       += (int)$r->ship_m;
        $order->ship_l       += (int)$r->ship_l;
        $order->ship_xl      += (int)$r->ship_xl;

        // Update status jika kiriman sudah memenuhi target PO
        if ($order->qty_terkirim >= $order->jumlah) {
            $order->status = 'selesai';
        }
        $order->save();

        // 4. Balikin JSON (Sangat Penting buat Pop-up Modal)
        return response()->json([
            'success' => true,
            'shipment_id' => $shipment->id,
            'message' => 'Surat Jalan berhasil dibuat!'
        ]);
    }

    /**
     * Cetak Surat Jalan
     */
    public function print($id)
    {
        $shipment = Shipment::with('order')->findOrFail($id);
        
        return view('order.print_surat_jalan', [
            'shipment' => $shipment,
            'order'    => $shipment->order
        ]);
    }
}