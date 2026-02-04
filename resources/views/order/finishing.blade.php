@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">✨ Input Hasil Finishing</h2>
        <span class="badge bg-info text-white px-3 py-2 shadow-sm">Divisi Finishing</span>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-3">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- KOLOM INPUT --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-info text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">✨ Form Finishing</h5>
                </div>
                <div class="card-body">
                    {{-- Ganti action ke production.update --}}
                    <form action="{{ route('production.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe" value="finishing">
                        <input type="hidden" name="variant_id" id="input-variant-id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">1. PILIH PO / ORDER</label>
                            <select id="select-po-finish" name="order_id" class="form-select border-info shadow-sm" required>
                                <option value="">-- Pilih Nomor PO --</option>
                                @foreach($orders as $o)
                                    <option value="{{ $o->id }}" 
                                            data-customer="{{ $o->customer }}"
                                            data-variants='@json($o->variants)'>
                                        {{ $o->kode_order }} - {{ $o->produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="area-warna-finish" style="display:none;">
                            <label class="form-label fw-bold text-info small">2. PILIH WARNA</label>
                            <select id="select-warna-finish" class="form-select border-info shadow-sm" required>
                                {{-- Diisi via JS --}}
                            </select>
                        </div>

                        <div id="input-detail-finish" style="display:none;">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small">3. SIZE</label>
                                    <select name="size_type" class="form-select border-dark" required>
                                        <option value="s">S</option>
                                        <option value="m">M</option>
                                        <option value="l">L</option>
                                        <option value="xl">XL</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold small">4. QTY (PCS)</label>
                                    <input type="number" name="qty_tambah" class="form-control border-dark" placeholder="0" required min="1">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-info w-100 text-white fw-bold shadow border-bottom border-4 border-dark py-3 mb-3 rounded-3">
                                <i class="fas fa-check-double me-2"></i> SIMPAN DATA FINISHING
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Stok Jahit --}}
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white fw-bold small">
                    📦 STOK SIAP FINISHING (DARI JAHIT)
                </div>
                <div class="card-body p-0">
                    <div id="info-stok-jahit" class="list-group list-group-flush">
                        <div class="p-4 text-center text-muted small">Pilih PO untuk melihat barang yang sudah dijahit</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM MONITORING --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Monitoring Finishing per Warna</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th class="ps-4">PO & Produk</th>
                                    <th>Warna</th>
                                    <th class="text-center">Total Target</th>
                                    <th class="text-center">Finishing In</th>
                                    <th class="text-center">Sisa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $o)
                                    @foreach($o->variants as $index => $v)
                                    @php 
                                        $totalFin = ($v->finishing_s + $v->finishing_m + $v->finishing_l + $v->finishing_xl);
                                        $sisa = $v->total - $totalFin;
                                    @endphp
                                    <tr>
                                        @if($index == 0)
                                        <td rowspan="{{ $o->variants->count() }}" class="ps-4 border-end bg-white">
                                            <b class="text-dark">{{ $o->kode_order }}</b><br>
                                            <small class="text-muted">{{ $o->customer }}</small>
                                        </td>
                                        @endif
                                        <td class="fw-bold text-info">{{ strtoupper($v->warna) }}</td>
                                        <td class="text-center fw-bold">{{ $v->total }}</td>
                                        <td class="text-center text-success fw-bold">{{ $totalFin }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $sisa <= 0 ? 'bg-success' : 'bg-light text-danger border' }}">
                                                {{ $sisa }} pcs
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectPo = document.getElementById('select-po-finish');
    const selectWarna = document.getElementById('select-warna-finish');
    const areaWarna = document.getElementById('area-warna-finish');
    const inputDetail = document.getElementById('input-detail-finish');
    const infoStok = document.getElementById('info-stok-jahit');
    const inputVariantId = document.getElementById('input-variant-id');
    
    let currentVariants = [];

    selectPo.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            currentVariants = JSON.parse(opt.getAttribute('data-variants'));
            
            selectWarna.innerHTML = '<option value="">-- Pilih Warna --</option>';
            infoStok.innerHTML = '';
            
            currentVariants.forEach(v => {
                selectWarna.innerHTML += `<option value="${v.id}">${v.warna.toUpperCase()}</option>`;
                
                // Info stok jahit (Sewing)
                infoStok.innerHTML += `
                    <div class="list-group-item border-start border-4 border-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark small">${v.warna.toUpperCase()}</strong>
                            <span class="badge bg-light text-info border">Total: ${v.total}</span>
                        </div>
                        <div class="d-flex gap-3 text-muted" style="font-size: 10px;">
                            <span>S: <b>${v.sewing_s}</b></span>
                            <span>M: <b>${v.sewing_m}</b></span>
                            <span>L: <b>${v.sewing_l}</b></span>
                            <span>XL: <b>${v.sewing_xl}</b></span>
                        </div>
                    </div>
                `;
            });

            areaWarna.style.display = 'block';
            inputDetail.style.display = 'none';
        } else {
            areaWarna.style.display = 'none';
            inputDetail.style.display = 'none';
            infoStok.innerHTML = '<div class="p-4 text-center text-muted small">Pilih PO untuk melihat barang yang sudah dijahit</div>';
        }
    });

    selectWarna.addEventListener('change', function() {
        if (this.value) {
            inputVariantId.value = this.value;
            inputDetail.style.display = 'block';
        } else {
            inputDetail.style.display = 'none';
        }
    });
});
</script>
@endsection