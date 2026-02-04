@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">🧵 Input Hasil Sewing (Jahit)</h2>
        <span class="badge bg-success px-3 py-2 shadow-sm">Divisi Sewing</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 15px;">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Nomor PO / Customer</label>
                    <select id="select-po" class="form-select form-select-lg shadow-sm border-success text-dark">
                        <option value="">-- Cari Kode Order atau Nama Customer --</option>
                        @foreach($orders as $o)
                            <option value="{{ $o->id }}" 
                                    data-customer="{{ $o->customer }}"
                                    data-produk="{{ $o->produk }}"
                                    data-s="{{ $o->sewing_s }}/{{ $o->cutting_s }}"
                                    data-m="{{ $o->sewing_m }}/{{ $o->cutting_m }}"
                                    data-l="{{ $o->sewing_l }}/{{ $o->cutting_l }}"
                                    data-xl="{{ $o->sewing_xl }}/{{ $o->cutting_xl }}">
                                {{ $o->kode_order }} - {{ $o->customer }} ({{ $o->produk }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="input-area" style="display: none;">
                    <hr class="my-4">
                    <div class="row bg-light p-4 rounded-4 mx-0 text-dark">
                        <div class="col-md-5 mb-4 mb-md-0 border-end">
                            <h5 class="fw-bold text-success mb-3"><i class="fas fa-info-circle me-2"></i>Informasi Produksi</h5>
                            <p class="mb-1 small text-muted">Customer: <strong class="text-dark" id="det-customer"></strong></p>
                            <p class="mb-3 small text-muted">Produk: <strong class="text-dark" id="det-produk"></strong></p>
                            
                            <h6 class="fw-bold mt-4 mb-2 small text-muted">Status Sewing (Progress/Hasil Cutting):</h6>
                            <div class="row text-center g-2">
                                <div class="col-3 bg-white p-2 rounded shadow-sm border small"><small class="d-block text-muted">S</small><span id="det-s" class="fw-bold"></span></div>
                                <div class="col-3 bg-white p-2 rounded shadow-sm border small"><small class="d-block text-muted">M</small><span id="det-m" class="fw-bold"></span></div>
                                <div class="col-3 bg-white p-2 rounded shadow-sm border small"><small class="d-block text-muted">L</small><span id="det-l" class="fw-bold"></span></div>
                                <div class="col-3 bg-white p-2 rounded shadow-sm border small"><small class="d-block text-muted">XL</small><span id="det-xl" class="fw-bold"></span></div>
                            </div>
                        </div>

                        <div class="col-md-7 ps-md-5">
                            <h5 class="fw-bold mb-4"><i class="fas fa-plus-circle me-2 text-success"></i>Update Hasil Jahit</h5>
                            <form action="{{ route('order.bulk') }}" method="POST">
                                @csrf
                                <input type="hidden" name="order_id" id="input-order-id">
                                <input type="hidden" name="tipe" value="produksi"> {{-- Tipe 'produksi' sesuai Controller --}}

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Pilih Size</label>
                                        <select name="size_type" class="form-select" required>
                                            <option value="s">SIZE S</option>
                                            <option value="m">SIZE M</option>
                                            <option value="l">SIZE L</option>
                                            <option value="xl">SIZE XL</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small">Jumlah Selesai (Pcs)</label>
                                        <input type="number" name="qty_tambah" class="form-control" placeholder="0" required min="1">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold py-3 shadow mt-3">
                                    <i class="fas fa-save me-2"></i>SIMPAN HASIL SEWING
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-header bg-white fw-bold text-danger">KENDALA JAHIT?</div>
                <div class="card-body">
                    <form action="{{ route('internal.request') }}" method="POST">
                        @csrf
                        <input type="hidden" name="divisi" value="Sewing">
                        <textarea name="pesan" class="form-control mb-3" rows="3" placeholder="Misal: Benang habis / Mesin rusak..." required></textarea>
                        <button type="submit" class="btn btn-danger w-100 fw-bold">LAPOR KE ADMIN</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPo = document.getElementById('select-po');
    const area = document.getElementById('input-area');

    selectPo.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (this.value) {
            document.getElementById('input-order-id').value = this.value;
            document.getElementById('det-customer').innerText = opt.getAttribute('data-customer');
            document.getElementById('det-produk').innerText = opt.getAttribute('data-produk');
            document.getElementById('det-s').innerText = opt.getAttribute('data-s');
            document.getElementById('det-m').innerText = opt.getAttribute('data-m');
            document.getElementById('det-l').innerText = opt.getAttribute('data-l');
            document.getElementById('det-xl').innerText = opt.getAttribute('data-xl');
            area.style.display = 'block';
        } else {
            area.style.display = 'none';
        }
    });
});
</script>
@endsection