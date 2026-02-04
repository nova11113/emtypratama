@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">✂️ Input Hasil Cutting (Potong)</h2>
        <span class="badge bg-primary px-3 py-2 shadow-sm">Divisi Cutting</span>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold text-muted">Cari PO atau Customer</label>
                        <select id="select-po" class="form-select form-select-lg shadow-sm border-primary">
                            <option value="">-- Pilih Nomor PO --</option>
                            @foreach($orders as $o)
                                <option value="{{ $o->id }}" 
                                        data-customer="{{ $o->customer }}"
                                        data-produk="{{ $o->produk }}"
                                        data-variants='@json($o->variants)'>
                                    {{ $o->kode_order }} - {{ $o->customer }} ({{ $o->produk }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Area Input Dinamis --}}
                <div id="input-area" style="display: none;">
                    <hr class="my-4">
                    <div class="row bg-light p-4 rounded-4 mx-0 text-dark border">
                        <div class="col-md-5 mb-4 mb-md-0 border-end">
                            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Status Varian</h5>
                            <p class="small mb-1 text-muted">Customer: <strong id="det-customer" class="text-dark"></strong></p>
                            <p class="small mb-3 text-muted">Produk: <strong id="det-produk" class="text-dark"></strong></p>
                            
                            <label class="form-label fw-bold small text-danger">1. Pilih Warna:</label>
                            <select id="select-warna" class="form-select border-primary mb-3 shadow-sm" required>
                                {{-- Diisi via JS --}}
                            </select>

                            <h6 class="fw-bold mt-4 mb-2 small text-uppercase">Target Per Size (PO):</h6>
                            <div class="row text-center g-2" id="status-size-warna">
                                <div class="col-12"><small class="text-muted">Pilih warna dulu...</small></div>
                            </div>
                        </div>

                        <div class="col-md-7 ps-md-5">
                            <h5 class="fw-bold mb-4 text-success"><i class="fas fa-edit me-2"></i>Update Hasil Potong</h5>
                            
                            {{-- INI KUNCI BIAR GAK 404: Manggil name route 'production.update' --}}
                            <form action="{{ route('production.update') }}" method="POST" id="form-cutting">
                                @csrf
                                <input type="hidden" name="order_id" id="input-order-id">
                                <input type="hidden" name="variant_id" id="input-variant-id">
                                <input type="hidden" name="tipe" value="cutting">

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-bold small text-primary">2. Bahan yang Digunakan</label>
                                        <select name="material_id" class="form-select border-primary" required>
                                            <option value="">-- Pilih Stok Kain --</option>
                                            @foreach($materials as $mat)
                                                <option value="{{ $mat->id }}">{{ $mat->nama_bahan }} (Stok: {{ $mat->stok }} {{ $mat->satuan }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">3. Pilih Size</label>
                                        <select name="size_type" class="form-select" required>
                                            <option value="s">S</option>
                                            <option value="m">M</option>
                                            <option value="l">L</option>
                                            <option value="xl">XL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">4. Jumlah (Pcs)</label>
                                        <input type="number" name="qty_tambah" class="form-control" placeholder="0" required min="1">
                                    </div>
                                    
                                    <div class="col-md-12 mb-4">
                                        <div class="p-3 bg-warning-subtle rounded border border-warning shadow-sm">
                                            <label class="form-label fw-bold small text-dark">Kain Terpakai (Roll/Meter)</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" name="jumlah_bahan" class="form-control border-warning" placeholder="0.00" required>
                                                <span class="input-group-text bg-warning border-warning fw-bold">Qty</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold py-3 shadow border-bottom border-4 border-dark">
                                    <i class="fas fa-save me-2"></i>SIMPAN HASIL POTONG
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPo = document.getElementById('select-po');
    const selectWarna = document.getElementById('select-warna');
    const inputVariantId = document.getElementById('input-variant-id');
    const area = document.getElementById('input-area');
    const statusSize = document.getElementById('status-size-warna');
    
    let variants = [];

    selectPo.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            document.getElementById('input-order-id').value = this.value;
            document.getElementById('det-customer').innerText = opt.getAttribute('data-customer');
            document.getElementById('det-produk').innerText = opt.getAttribute('data-produk');
            
            try {
                variants = JSON.parse(opt.getAttribute('data-variants'));
                selectWarna.innerHTML = '<option value="">-- Pilih Warna --</option>';
                variants.forEach(v => {
                    selectWarna.innerHTML += `<option value="${v.id}">${v.warna.toUpperCase()}</option>`;
                });
                area.style.display = 'block';
                statusSize.innerHTML = '<div class="col-12 small text-muted">Pilih warna dulu bro...</div>';
            } catch (e) {
                console.error("Data variant bermasalah", e);
            }
        } else {
            area.style.display = 'none';
        }
    });

    selectWarna.addEventListener('change', function() {
        const vId = this.value;
        if(inputVariantId) {
            inputVariantId.value = vId;
        }

        const dataV = variants.find(i => i.id == vId);
        if (dataV) {
            statusSize.innerHTML = `
                <div class="col-3"><div class="bg-white p-2 rounded shadow-sm border small"><small class="text-muted d-block">S</small><b>${dataV.s}</b></div></div>
                <div class="col-3"><div class="bg-white p-2 rounded shadow-sm border small"><small class="text-muted d-block">M</small><b>${dataV.m}</b></div></div>
                <div class="col-3"><div class="bg-white p-2 rounded shadow-sm border small"><small class="text-muted d-block">L</small><b>${dataV.l}</b></div></div>
                <div class="col-3"><div class="bg-white p-2 rounded shadow-sm border small"><small class="text-muted d-block">XL</small><b>${dataV.xl}</b></div></div>
            `;
        }
    });
});
</script>
@endsection