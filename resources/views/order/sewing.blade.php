@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">🪡 Input Hasil Sewing (Jahit)</h2>
        <span class="badge bg-warning text-dark px-3 py-2 shadow-sm">Divisi Sewing</span>
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
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-warning text-dark fw-bold" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">🪡 Form Input Jahit</h5>
                </div>
                <div class="card-body">
                    {{-- Action mengarah ke rute production.update --}}
                    <form action="{{ route('production.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe" value="produksi"> {{-- 'produksi' akan dimapping ke 'sewing' di Controller --}}
                        <input type="hidden" name="variant_id" id="input-variant-id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small">1. PILIH NOMOR PO</label>
                            <select id="select-po-sewing" name="order_id" class="form-select form-select-lg border-warning shadow-sm" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($orders as $o)
                                    <option value="{{ $o->id }}" 
                                            data-customer="{{ $o->customer }}"
                                            data-produk="{{ $o->produk }}"
                                            data-variants='@json($o->variants)'>
                                        {{ $o->kode_order }} - {{ $o->customer }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="area-warna" style="display:none;">
                            <label class="form-label fw-bold text-primary small">2. PILIH WARNA</label>
                            <select id="select-warna-sewing" class="form-select form-select-lg border-primary shadow-sm" required>
                                {{-- Diisi via JS --}}
                            </select>
                        </div>

                        <div id="input-detail-sewing" style="display:none;">
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

                            <button type="submit" class="btn btn-warning w-100 py-3 fw-bold shadow border-bottom border-4 border-dark mt-2">
                                <i class="fas fa-save me-2"></i>SIMPAN HASIL JAHIT
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Widget Info Progress per Warna --}}
            <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between" style="font-size: 11px;">
                    <span>📊 TARGET POTONG PER WARNA</span>
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="card-body p-0">
                    <div id="target-warna-list" class="list-group list-group-flush">
                        <div class="p-4 text-center text-muted small">Pilih PO untuk melihat stok kain yang siap dijahit</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Antrean Kerja Sewing</h5>
                    <span class="badge bg-warning text-dark">{{ $orders->count() }} PO Sedang Berjalan</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr class="small text-uppercase">
                                    <th class="ps-4">Order / Produk</th>
                                    <th class="text-center">Total Target</th>
                                    <th>Status Progress per Warna (Sewing)</th>
                                    <th class="text-center">Progress Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $o)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $o->kode_order }}</div>
                                        <div class="badge bg-light text-muted border">{{ $o->produk }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary">{{ $o->jumlah }} <small class="text-muted">pcs</small></td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($o->variants as $v)
                                                <span class="badge border text-dark bg-white shadow-sm" style="font-size: 10px;">
                                                    <i class="fas fa-palette me-1 text-muted"></i>{{ strtoupper($v->warna) }}: 
                                                    <span class="text-primary">{{ $v->sewing_s + $v->sewing_m + $v->sewing_l + $v->sewing_xl }}</span> 
                                                    / {{ $v->total }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php $pct = ($o->jumlah > 0) ? ($o->qty_produksi / $o->jumlah) * 100 : 0; @endphp
                                        <div class="progress mb-1" style="height: 6px; width: 80px; margin: 0 auto;">
                                            <div class="progress-bar bg-warning" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <small class="fw-bold text-dark" style="font-size: 10px;">{{ round($pct) }}%</small>
                                    </td>
                                </tr>
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
    const selectPo = document.getElementById('select-po-sewing');
    const selectWarna = document.getElementById('select-warna-sewing');
    const areaWarna = document.getElementById('area-warna');
    const inputDetail = document.getElementById('input-detail-sewing');
    const targetList = document.getElementById('target-warna-list');
    const inputVariantId = document.getElementById('input-variant-id');
    
    let currentVariants = [];

    selectPo.addEventListener('change', function() {
        if (this.value) {
            const opt = this.options[this.selectedIndex];
            currentVariants = JSON.parse(opt.getAttribute('data-variants'));
            
            selectWarna.innerHTML = '<option value="">-- Pilih Warna --</option>';
            targetList.innerHTML = '';
            
            currentVariants.forEach(v => {
                selectWarna.innerHTML += `<option value="${v.id}">${v.warna.toUpperCase()}</option>`;
                
                // Tampilkan target cutting sebagai acuan orang jahit
                targetList.innerHTML += `
                    <div class="list-group-item border-start border-4 border-warning">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark small">${v.warna.toUpperCase()}</strong>
                            <span class="badge bg-light text-dark border">Target: ${v.total}</span>
                        </div>
                        <div class="d-flex gap-3 text-muted" style="font-size: 10px;">
                            <span>S: <b>${v.cutting_s}</b></span>
                            <span>M: <b>${v.cutting_m}</b></span>
                            <span>L: <b>${v.cutting_l}</b></span>
                            <span>XL: <b>${v.cutting_xl}</b></span>
                        </div>
                        <small class="text-muted" style="font-size: 9px;">*Angka di atas adalah hasil potong yang siap dijahit</small>
                    </div>
                `;
            });

            areaWarna.style.display = 'block';
            inputDetail.style.display = 'none';
        } else {
            areaWarna.style.display = 'none';
            inputDetail.style.display = 'none';
            targetList.innerHTML = '<div class="p-4 text-center text-muted small">Pilih PO untuk melihat stok kain</div>';
        }
    });

    selectWarna.addEventListener('change', function() {
        if (this.value) {
            inputVariantId.value = this.value;
            inputDetail.style.display = 'block';
            console.log("Variant ID Sewing Set to:", this.value);
        } else {
            inputDetail.style.display = 'none';
        }
    });
});
</script>
@endsection