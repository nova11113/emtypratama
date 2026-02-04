@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">🛒 Riwayat Pembelian Bahan</h4>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBeliBahan">
            + Buat Pesanan Baru
        </button>
    </div>

    <div class="card shadow border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tgl Order</th>
                            <th>Bahan</th>
                            <th>Supplier</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchases as $p)
                        <tr>
                            <td>{{ $p->created_at->format('d/m/Y') }}</td>
                            <td class="fw-bold">{{ $p->material->nama_bahan }}</td>
                            <td>{{ $p->supplier }}</td>
                            <td>
                                {{ $p->jumlah }} 
                                <span class="badge bg-light text-dark border">{{ $p->satuan }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $p->status == 'pending' ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ $p->status == 'pending' ? '⏳ Menunggu' : '✅ Selesai' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($p->status == 'pending')
                                    <form action="{{ route('procurement.terima', $p->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Konfirmasi Datang</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBeliBahan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Catat Pembelian Bahan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('procurement.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Bahan Baku</label>
                        <select name="material_id" id="material_select" class="form-select" required>
                            <option value="">-- Pilih Kain / Aksesoris --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}" data-satuan="{{ $m->satuan }}">
                                    {{ $m->nama_bahan }} (Gudang: {{ $m->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="supplier" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <label class="form-label">Jumlah Beli</label>
                            <input type="number" name="jumlah" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="satuan" id="satuan_input" class="form-control bg-light" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success w-100">Simpan Pesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    // SCRIPT OTOMATIS GANTI SATUAN
    document.getElementById('material_select').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var satuan = selectedOption.getAttribute('data-satuan');
        document.getElementById('satuan_input').value = satuan ? satuan : '';
    });
</script>
@endsection