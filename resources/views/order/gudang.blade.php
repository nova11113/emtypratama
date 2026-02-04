@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0">📦 Gudang Barang Jadi (Siap Kirim)</h2>
        <span class="badge bg-success shadow-sm px-3 py-2">Stok Real-Time</span>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-start ps-4 py-3">PO & Produk</th>
                            <th>Size S</th>
                            <th>Size M</th>
                            <th>Size L</th>
                            <th>Size XL</th>
                            <th class="bg-secondary text-white">Sisa Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                        @php 
                            $stokS = (int)$o->qc_s - (int)$o->ship_s; 
                            $stokM = (int)$o->qc_m - (int)$o->ship_m;
                            $stokL = (int)$o->qc_l - (int)$o->ship_l;
                            $stokXL = (int)$o->qc_xl - (int)$o->ship_xl;
                            $sisaStokReal = $stokS + $stokM + $stokL + $stokXL;
                        @endphp

                        @if($sisaStokReal > 0)
                        <tr>
                            <td class="text-start ps-4">
                                <b>{{ $o->kode_order }}</b><br><small>{{ $o->customer }} - {{ $o->produk }}</small>
                            </td>
                            <td><span class="badge bg-info text-dark border">{{ $stokS }}</span></td>
                            <td><span class="badge bg-info text-dark border">{{ $stokM }}</span></td>
                            <td><span class="badge bg-info text-dark border">{{ $stokL }}</span></td>
                            <td><span class="badge bg-info text-dark border">{{ $stokXL }}</span></td>
                            <td class="fw-bold text-success bg-light">{{ $sisaStokReal }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary fw-bold" 
                                        onclick="openInputModal('{{ $o->id }}', '{{ $o->kode_order }}', '{{ $stokS }}', '{{ $stokM }}', '{{ $stokL }}', '{{ $stokXL }}')">
                                    🚚 Kirim Barang
                                </button>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL INPUT PENGIRIMAN --}}
<div class="modal fade" id="inputModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Input Pengiriman: <span id="modal_kode_order"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPengiriman">
                @csrf
                <input type="hidden" name="order_id" id="modal_order_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Nama Ekspedisi / Supir</label>
                        <input type="text" name="ekspedisi" class="form-control" placeholder="Contoh: JNE / Bpk. Agus" required>
                    </div>
                    <p class="text-muted small mb-2">*Masukkan jumlah barang yang dikirim:</p>
                    <table class="table table-bordered text-center align-middle">
                        <thead class="bg-light">
                            <tr><th>Size</th><th>Gudang</th><th width="130">Kirim</th></tr>
                        </thead>
                        <tbody id="input_rows"></tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnSimpan">Simpan & Cetak Surat Jalan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL PRINT SURAT JALAN --}}
<div class="modal fade" id="printModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white text-center">
                <h5 class="modal-title w-100 fw-bold">Preview Surat Jalan</h5>
            </div>
            <div class="modal-body p-0 text-center">
                <iframe id="printFrame" src="" style="width: 100%; height: 70vh; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fw-bold px-4" onclick="executePrint()">🖨️ Cetak Sekarang</button>
                <button type="button" class="btn btn-secondary fw-bold" onclick="location.reload()">Selesai & Update Gudang</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.inputModal = new bootstrap.Modal(document.getElementById('inputModal'));
        window.printModal = new bootstrap.Modal(document.getElementById('printModal'));
    });

    function openInputModal(id, kode, s, m, l, xl) {
        document.getElementById('modal_order_id').value = id;
        document.getElementById('modal_kode_order').innerText = kode;
        
        let rows = '';
        const sizes = { S: s, M: m, L: l, XL: xl };
        
        for (let key in sizes) {
            rows += `<tr>
                <td class="fw-bold">SIZE ${key}</td>
                <td class="text-primary fw-bold">${sizes[key]} Pcs</td>
                <td>
                    <input type="number" name="ship_${key.toLowerCase()}" 
                           class="form-control text-center border-primary" 
                           min="0" max="${sizes[key]}" value="0">
                </td>
            </tr>`;
        }
        document.getElementById('input_rows').innerHTML = rows;
        window.inputModal.show();
    }

    document.getElementById('formPengiriman').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpan');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        const formData = new FormData(this);

        // Gunakan named route agar sinkron dengan web.php
        fetch("{{ route('order.pengirimanStore') }}", {
            method: "POST",
            body: formData,
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success && data.shipment_id) {
                window.inputModal.hide();
                
                // --- PERBAIKAN URL PRATINJAU AGAR TIDAK 404 ---
                let printUrl = "{{ route('order.suratJalan', ':id') }}";
                printUrl = printUrl.replace(':id', data.shipment_id);
                
                document.getElementById('printFrame').src = printUrl;
                window.printModal.show();
            } else {
                alert('Gagal: ' + (data.message || 'Cek stok atau inputan lu bro.'));
                btn.disabled = false;
                btn.innerText = 'Simpan & Cetak Surat Jalan';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Sistem Error. Pastikan Controller lu balikin JSON.');
            btn.disabled = false;
            btn.innerText = 'Simpan & Cetak Surat Jalan';
        });
    });

    function executePrint() {
        const frame = document.getElementById('printFrame');
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }
</script>
@endsection