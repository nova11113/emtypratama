<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Purchasing;
use App\Models\MaterialRequest; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // --- 1. MANAJEMEN STOK (INDEX & STORE) ---

    public function index() {
        return view('inventory.index', ['materials' => Material::all()]);
    }

    // FIX: Fungsi simpan bahan baru yang bikin error Call to undefined method
    public function inventoryStore(Request $request) {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'satuan' => 'required',
        ]);

        Material::create([
            'nama_bahan'   => $request->nama_bahan,
            'kategori'     => $request->kategori,
            'satuan'       => $request->satuan,
            'stok'         => $request->stok,
            'minimal_stok' => $request->minimal_stok ?? 10,
        ]);

        return back()->with('success', 'Bahan baru berhasil ditambahkan!');
    }

    // --- 2. PERMINTAAN BAHAN (REQUEST & APPROVE) ---

    public function requestIndex() {
        $requests = MaterialRequest::with(['material', 'user'])->latest()->get();
        $materials = Material::where('stok', '>', 0)->get();
        
        return view('inventory.request', compact('requests', 'materials'));
    }

    public function requestStore(Request $r) {
        $r->validate([
            'material_id' => 'required|exists:materials,id',
            'qty_minta' => 'required|numeric|min:0.01',
            'keperluan' => 'required|string'
        ]);

        MaterialRequest::create([
            'kode_request' => 'REQ-' . date('Ymd') . '-' . strtoupper(uniqid()),
            'material_id' => $r->material_id,
            'qty_minta' => $r->qty_minta,
            'keperluan' => $r->keperluan,
            'status' => 'pending',
            'user_id' => auth()->id()
        ]);

        return back()->with('success', 'Permintaan bahan berhasil terkirim ke Gudang!');
    }

    public function requestApprove($id) {
        try {
            DB::beginTransaction();

            $req = MaterialRequest::findOrFail($id);
            $material = Material::findOrFail($req->material_id);

            if ($material->stok < $req->qty_minta) {
                return back()->with('error', "Gagal! Stok {$material->nama_bahan} tidak cukup.");
            }

            // Potong Stok & Catat Log
            $material->decrement('stok', $req->qty_minta);
            MaterialLog::create([
                'material_id' => $material->id,
                'tipe' => 'keluar',
                'jumlah' => $req->qty_minta,
                'keterangan' => "Disetujui REQ: {$req->kode_request} - {$req->keperluan}"
            ]);

            $req->update(['status' => 'disetujui']);

            DB::commit();
            return back()->with('success', 'Permintaan disetujui & Stok otomatis terpotong!');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // --- 3. PENGADAAN & HISTORY ---

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
