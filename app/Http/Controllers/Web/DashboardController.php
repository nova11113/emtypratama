<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductionLog;
use App\Models\InternalRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request) {
        $tanggal = $request->get('tanggal', now()->toDateString());
        $orders = Order::latest()->get();
        
        return view('dashboard', [
            'orders'        => $orders,
            'total_order'   => $orders->sum('jumlah') - $orders->sum('qty_terkirim'),
            'total_sewing'  => $orders->sum('qty_produksi'),
            'total_qc'      => $orders->sum('qty_qc'),
            'dailyReports'  => ProductionLog::with('order')->whereDate('tanggal', $tanggal)->latest()->get(),
            'notif_chat'    => InternalRequest::where('is_read', false)->count(),
            'tanggal'       => $tanggal
        ]);
    }
    public function riwayatRequest() {
    // Ambil semua pesan, urutkan dari yang paling baru
    $semua_pesan = \App\Models\InternalRequest::latest()->paginate(20);

    return view('internal.riwayat', compact('semua_pesan'));
}
public function storeRequest(Request $request) {
    $request->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    \App\Models\InternalRequest::create([
        'user_id' => auth()->id(),
        'subject' => $request->subject,
        'message' => $request->message,
        'status'  => 'pending',
        'is_read' => false
    ]);

    return back()->with('success', 'Pesan berhasil dikirim ke Admin!');
}
}