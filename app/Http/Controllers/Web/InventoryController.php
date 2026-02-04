<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Purchasing;
use App\Models\MaterialRequest; // Pastikan lu udah buat Model ini
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    // 1. List Stok Bahan Baku
    public function index() {
        return view('inventory.index', ['materials' => Material::all()]);
    }

    // 2. Halaman Permintaan Bahan (Request & Approve) - FIX ERROR TADI
    public function requestIndex() {
        // Ambil permintaan yang pending di atas, lalu yang sudah disetujui
        $requests = MaterialRequest::with(['material', 'user'])->latest()->get();
        $materials = Material::where('stok', '>', 0)->get();
        
        return view('inventory.request', compact('requests', 'materials'));
    }

    // 3. Simpan Permintaan Baru (Dari Produksi)
    public function requestStore(Request $r) {
        $r->validate([
            'material_id' => 'required|exists:materials,id',
            'qty_minta' => 'required|numeric|min:0.01',
            'keperluan' => 'required|string'
        ]);

        MaterialRequest::create([
            'kode_request' => 'REQ-' . date('Ymd') . '-' . strtoupper(uniqid(4)),
            'material_id' => $r->material_id,
            'qty_minta' => $r->qty_minta,
            'keperluan' => $r->keperluan,
            'status' => 'pending',
            'user_id' => auth()->id()
        ]);

        return back()->with('success', 'Permintaan bahan berhasil terkirim ke Gudang!');
    }

    // 4. Setujui Permintaan & Potong Stok Otomatis
    public function requestApprove($id) {
        $req = MaterialRequest::findOrFail($id);
        $material = Material::findOrFail($req->material_id);

        // Cek apakah stok di gudang cukup
        if ($material->stok < $req->qty_minta) {
            return back()->with('error', "Gagal! Stok {$material->nama_bahan} tidak cukup.");
        }

        // A. Potong Stok Material
        $material->decrement('stok', $req->qty_minta);

        // B. Catat di MaterialLog (Biar history sinkron)
        MaterialLog::create([
            'material_id' => $material->id,
            'tipe' => 'keluar',
            'jumlah' => $req->qty_minta,
            'keterangan' => "Disetujui REQ: {$req->kode_request} - {$req->keperluan}"
        ]);

        // C. Update Status Request
        $req->update(['status' => 'disetujui']);

        return back()->with('success', 'Permintaan disetujui & Stok otomatis terpotong!');
    }

    // --- FUNGSI BAWAAN LU YANG LAIN (TETAP SAMA) ---

    public function procurement() {
        return view('procurement.index', [
            'purchases' => Purchasing::with('material')->latest()->get(),
            'materials' => Material::all()
        ]);
    }

    public function terimaBahan($id) {
        $pembelian = Purchasing::findOrFail($id);
        if($pembelian->status == 'diterima') return back()->with('error', 'Sudah diterima!');

        $pembelian->update(['status' => 'diterima']);
        $material = Material::findOrFail($pembelian->material_id);
        $material->increment('stok', $pembelian->jumlah);

        MaterialLog::create([
            'material_id' => $pembelian->material_id,
            'tipe' => 'masuk',
            'jumlah' => $pembelian->jumlah,
            'keterangan' => "Penerimaan PO dari: " . $pembelian->supplier
        ]);

        return back()->with('success', 'Stok berhasil masuk gudang!');
    }

    public function inventoryHistory($id) {
        $material = Material::findOrFail($id);
        $logs = MaterialLog::where('material_id', $id)->latest()->paginate(20);
        return view('inventory.history', compact('material', 'logs'));
    }

    public function inventoryUpdate(Request $request, $id) {
        $request->validate(['stok_baru' => 'required|numeric', 'keterangan' => 'nullable|string']);
        $material = Material::findOrFail($id);
        $stok_lama = $material->stok;
        $selisih = $request->stok_baru - $stok_lama;
        $material->stok = $request->stok_baru;
        $material->save();

        MaterialLog::create([
            'material_id' => $id,
            'tipe' => ($selisih >= 0) ? 'masuk' : 'keluar',
            'jumlah' => abs($selisih),
            'keterangan' => $request->keterangan ?? "Penyesuaian stok manual"
        ]);
        return back()->with('success', "Stok berhasil diupdate!");
    }
}