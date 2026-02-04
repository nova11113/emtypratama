@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🚚 Input Pengiriman Baru - {{ $order->kode_order }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pengiriman.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="small text-muted">Customer</label>
                                <p class="fw-bold">{{ $order->customer }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <label class="small text-muted">Stok Tersedia (QC)</label>
                                <p class="text-success fw-bold">{{ $order->qty_qc - $order->qty_terkirim }} Pcs</p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Size</th>
                                        <th>Stok Gudang</th>
                                        <th width="200">Jumlah Kirim</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(['s', 'm', 'l', 'xl'] as $sz)
                                    @php 
                                        $stokSekarang = $order->{'qc_'.$sz} - $order->{'ship_'.$sz}; 
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-uppercase">{{ $sz }}</td>
                                        <td><span class="badge bg-info">{{ $stokSekarang }}</span></td>
                                        <td>
                                            <input type="number" name="ship_{{ $sz }}" 
                                                   class="form-control text-center" 
                                                   min="0" max="{{ $stokSekarang }}" 
                                                   value="0">
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan & Buat Surat Jalan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection