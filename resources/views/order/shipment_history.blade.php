@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📜 Riwayat Pengiriman</h2>
        <div>
            <a href="{{ route('gudang.index') }}" class="btn btn-primary shadow-sm">📦 Ke Stok Gudang</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-primary text-white text-center">
                <small>Total Surat Jalan</small>
                <h4 class="fw-bold mb-0">{{ $shipments->count() }}</h4>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-success text-white text-center">
                <small>Total Pcs Terkirim</small>
                <h4 class="fw-bold mb-0">{{ number_format($shipments->sum('total')) }} Pcs</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Tgl Kirim</th>
                            <th>No. Surat Jalan</th>
                            <th class="text-start">Customer & Produk</th>
                            <th>Ekspedisi</th>
                            <th class="bg-secondary text-white">Total Qty</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $s)
                        <tr>
                            <td>{{ $s->created_at->format('d/m/Y') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $s->no_surat_jalan }}</span></td>
                            <td class="text-start">
                                <b>{{ $s->order->customer ?? 'N/A' }}</b><br>
                                <small class="text-muted">{{ $s->order->produk ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $s->ekspedisi ?? '-' }}</td>
                            <td class="fw-bold text-primary bg-light">{{ $s->total }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold" onclick="openPrintModal({{ $s->id }})">
                                    🖨️ Pop-up Print
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-4 text-muted">Belum ada riwayat pengiriman.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL PRINT --}}
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Pratinjau Surat Jalan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="printFrame" src="" style="width: 100%; height: 650px; border: none;"></iframe>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary fw-bold" onclick="executePrint()">Cetak Sekarang</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openPrintModal(id) {
        // Trik biar URL dinamis sesuai web.php dan nggak 404
        let url = "{{ route('order.suratJalan', ':id') }}";
        url = url.replace(':id', id);
        
        // Set source iframe
        document.getElementById('printFrame').src = url;
        
        // Tampilkan Modal
        var myModal = new bootstrap.Modal(document.getElementById('printModal'));
        myModal.show();
    }

    function executePrint() {
        const frame = document.getElementById('printFrame');
        if (frame.contentWindow) {
            frame.contentWindow.focus();
            frame.contentWindow.print();
        } else {
            alert('Gagal memuat pratinjau print.');
        }
    }
</script>
@endsection