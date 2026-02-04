@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 text-center">
                    <h3 class="fw-bold">🚚 Buat Pengiriman</h3>
                    <p class="text-muted">PO: <strong>{{ $order->kode_order }}</strong> (Stok Gudang: {{ $order->qty_qc - $order->qty_terkirim }} Pcs)</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="/pengiriman/store" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Ekspedisi / Kurir</label>
                            <input type="text" name="ekspedisi" class="form-control" placeholder="Contoh: JNE, J&T, atau Nama Supir" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jumlah yang Dikirim (Pcs)</label>
                            <input type="number" name="qty_kirim" class="form-control form-control-lg" max="{{ $order->qty_qc - $order->qty_terkirim }}" required>
                            <small class="text-danger">*Maksimal pengiriman sesuai stok gudang.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Keterangan / Alamat</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Kirim ke Gudang Pusat Jakarta"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                KONFIRMASI PENGIRIMAN
                            </button>
                            <a href="/gudang" class="btn btn-light fw-bold">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection