@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">🔍 Quality Control (QC)</h2>
        <span class="badge bg-dark px-3 py-2 shadow-sm">Divisi QC & Final Check</span>
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
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white fw-bold" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 text-white">🔍 Input Hasil QC</h5>
                </div>
                <div class="card-body text-dark">
                    {{-- Action diarahkan ke rute production.update --}}
                    <form action="{{ route('production.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe" value="qc">
                        <input type="hidden" name="variant_id" id="input-variant-id">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">1. PILIH NOMOR PO</label>
                            <select id="select-po-qc" name="order_id" class="form-select border-dark shadow-sm" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($orders as $o)
                                    <option value="{{ $o->id }}" 
                                            data-variants='@json($o->variants)'>
                                        {{ $o->kode_order }} - {{ $o->produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3" id="area-warna-qc" style="display:none;">
                            <label class="form-label fw-bold text-primary small">2. PILIH WARNA</label>
                            <select id="select-warna-qc" class="form-select border-primary shadow-sm" required>
                                {{-- Diisi via JS --}}
                            </select>
                        </div>

                        <div id="input-detail-qc" style="display:none;">
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
                                    <label class="form-label fw-bold small">4. JUMLAH (PCS)</label>
                                    <input type="number" name="qty_tambah" class="form-control border-dark" placeholder="0" required min="1">
                                </div>
                            </div>

                            <div class="mb-3 p-3 bg-light rounded border border-warning shadow-sm">
                                <label class="form-label fw-bold small text-muted d-block mb-2 text-uppercase">Kondisi Barang:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_reject" value="tidak" id="bagus" checked>
                                        <label class="form-check-label text-success fw-bold" for="bagus">PASSED (LOLOS)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="is_reject" value="ya" id="rijek">
                                        <label class="form-check-label text-danger fw-bold" for="rijek">REJECT (RIJEK)</label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-dark w-100 fw-bold py-3 shadow border-bottom border-4 border-primary">
                                <i class="fas fa-save me-2"></i>SIMPAN DATA QC
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info Stok Finishing --}}
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white fw-bold small">
                    👕 STOK DI MEJA QC (DARI FINISHING)
                </div>
                <div class="card-body p-0">
                    <div id="info-stok-qc" class="list-group list-group-flush">
                        <div class="p-4 text-center text-muted small">Pilih PO untuk melihat barang yang siap di-check</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0 text-white">Monitoring Hasil QC per Warna</h5>
                </div>
                <div class="card-body p-0 text-dark">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center">
                            <thead class="table-light text-uppercase small">
                                <tr>
                                    <th class="text-start ps-3">Warna / Produk</th>
                                    <th class="text-success">Lolos (Passed)</th>
                                    <th class="text-danger">Rijek (Reject)</th>
                                    <th>Status Kerja</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $o)
                                    @foreach($o->variants as $v)
                                    @php 
                                        $passed = ($v->qc_s + $v->qc_m + $v->qc_l + $v->qc_xl);
                                        $sisa = ($v->finishing_s + $v->finishing_m + $v->finishing_l + $v->finishing_xl);
                                    @endphp
                                    <tr>
                                        <td class="text-start ps-3">
                                            <b class="text-dark">{{ strtoupper($v->warna) }}</b><br>
                                            <small class="text-muted">{{ $o->kode_order }} - {{ $o->produk }}</small>
                                        </td>
                                        <td class="text-success fw-bold">{{ $passed }} pcs</td>
                                        <td class="text-danger fw-bold">0 pcs</td>
                                        <td>
                                            @if($sisa > 0)
                                                <span class="badge bg-warning text-dark px-3">Antre {{ $sisa }} Pcs</span>
                                            @else
                                                <span class="badge bg-success px-3 text-white">DONE</span>
                                            @endif
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
    const selectPo = document.getElementById('select-po-qc');
    const selectWarna = document.getElementById('select-warna-qc');
    const areaWarna = document.getElementById('area-warna-qc');
    const inputDetail = document.getElementById('input-detail-qc');
    const infoStok = document.getElementById('info-stok-qc');
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
                
                infoStok.innerHTML += `
                    <div class="list-group-item border-start border-4 border-primary">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-dark small">${v.warna.toUpperCase()}</strong>
                            <span class="badge bg-light text-primary border">Siap Cek: ${v.finishing_s + v.finishing_m + v.finishing_l + v.finishing_xl}</span>
                        </div>
                        <div class="d-flex gap-3 text-muted" style="font-size: 10px;">
                            <span>S: <b>${v.finishing_s}</b></span>
                            <span>M: <b>${v.finishing_m}</b></span>
                            <span>L: <b>${v.finishing_l}</b></span>
                            <span>XL: <b>${v.finishing_xl}</b></span>
                        </div>
                    </div>
                `;
            });

            areaWarna.style.display = 'block';
            inputDetail.style.display = 'none';
        } else {
            areaWarna.style.display = 'none';
            inputDetail.style.display = 'none';
            infoStok.innerHTML = '<div class="p-4 text-center text-muted small">Pilih PO untuk melihat barang</div>';
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