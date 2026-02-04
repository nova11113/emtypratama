@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Rincian Warna & Size: {{ $order->kode_order }}</h2>
        <a href="{{ route('order.index') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="row">
        {{-- SISI KIRI: TABEL DINAMIS WARNA & SIZE --}}
        <div class="col-md-9">
            <div class="card shadow border-0 mb-4" style="border-radius: 15px;">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-palette me-1"></i> Input Rincian Varian Warna
                </div>
                <div class="card-body p-4">
                    <form action="{{ url('/order/'.$order->id.'/update-variants') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="variantTable">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th style="width: 25%;">Warna</th>
                                        <th>S</th>
                                        <th>M</th>
                                        <th>L</th>
                                        <th>XL</th>
                                        <th style="width: 15%;">Subtotal</th>
                                        <th style="width: 5%;">#</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Tampilkan data lama kalau sudah ada --}}
                                    @forelse($order->variants as $v)
                                    <tr>
                                        <td><input type="text" name="warna[]" class="form-control" placeholder="Misal: Navy" value="{{ $v->warna }}" required></td>
                                        <td><input type="number" name="s[]" class="form-control text-center size-input" value="{{ $v->s }}" min="0"></td>
                                        <td><input type="number" name="m[]" class="form-control text-center size-input" value="{{ $v->m }}" min="0"></td>
                                        <td><input type="number" name="l[]" class="form-control text-center size-input" value="{{ $v->l }}" min="0"></td>
                                        <td><input type="number" name="xl[]" class="form-control text-center size-input" value="{{ $v->xl }}" min="0"></td>
                                        <td class="text-center fw-bold row-total">0</td>
                                        <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                    @empty
                                    {{-- Baris kosong pertama kalau data baru --}}
                                    <tr>
                                        <td><input type="text" name="warna[]" class="form-control" placeholder="Misal: Hitam" required></td>
                                        <td><input type="number" name="s[]" class="form-control text-center size-input" value="0" min="0"></td>
                                        <td><input type="number" name="m[]" class="form-control text-center size-input" value="0" min="0"></td>
                                        <td><input type="number" name="l[]" class="form-control text-center size-input" value="0" min="0"></td>
                                        <td><input type="number" name="xl[]" class="form-control text-center size-input" value="0" min="0"></td>
                                        <td class="text-center fw-bold row-total">0</td>
                                        <td><button type="button" class="btn btn-outline-danger btn-sm remove-row disabled"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button type="button" class="btn btn-dark btn-sm rounded-pill" id="addRow">
                                <i class="fas fa-plus me-1"></i> Tambah Warna Baru
                            </button>
                            <div class="text-end">
                                <h5 class="fw-bold mb-0 text-primary">Grand Total: <span id="grandTotal">0</span> / {{ number_format($order->jumlah) }} Pcs</h5>
                                <small id="statusWarning" class="fw-bold"></small>
                            </div>
                        </div>

                        <hr>
                        <div class="d-grid">
                            <button type="submit" id="btnSimpan" class="btn btn-primary btn-lg shadow rounded-pill">
                                <i class="fas fa-save me-1"></i> Simpan Semua Varian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: PREVIEW GAMBAR & SIZE CHART --}}
        <div class="col-md-3">
            <div class="card shadow border-0 p-2 mb-4 text-center" style="border-radius: 15px;">
                <label class="small fw-bold text-muted mb-2">MODEL</label>
                @if($order->image)
                    <img src="{{ asset('uploads/orders/' . $order->image) }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                @endif
            </div>

            <div class="card shadow border-0 p-2 text-center" style="border-radius: 15px;">
                <label class="small fw-bold text-muted mb-2">SIZE CHART</label>
                @if($order->size_chart)
                    <img src="{{ asset('uploads/charts/' . $order->size_chart) }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                @else
                    <div class="py-4 bg-light rounded small text-muted">Belum ada chart</div>
                @endif
                <form action="{{ url('/order/'.$order->id.'/update-chart') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                    @csrf
                    <input type="file" name="size_chart" class="form-control form-control-sm" onchange="this.form.submit()">
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    const tableBody = document.querySelector('#variantTable tbody');
    const addRowBtn = document.getElementById('addRow');
    const target = {{ $order->jumlah }};

    function calculate() {
        let grandTotal = 0;
        document.querySelectorAll('#variantTable tbody tr').forEach(row => {
            let subtotal = 0;
            row.querySelectorAll('.size-input').forEach(input => {
                subtotal += parseInt(input.value) || 0;
            });
            row.querySelector('.row-total').innerText = subtotal;
            grandTotal += subtotal;
        });

        document.getElementById('grandTotal').innerText = grandTotal.toLocaleString();
        const warning = document.getElementById('statusWarning');

        if(grandTotal > target) {
            warning.innerText = '⚠️ Kelebihan ' + (grandTotal - target) + ' pcs!';
            warning.className = 'text-danger fw-bold';
        } else if (grandTotal < target) {
            warning.innerText = '📉 Kurang ' + (target - grandTotal) + ' pcs';
            warning.className = 'text-warning fw-bold';
        } else {
            warning.innerText = '✅ Pas Sesuai Target';
            warning.className = 'text-success fw-bold';
        }
    }

    addRowBtn.addEventListener('click', () => {
        const row = `
        <tr>
            <td><input type="text" name="warna[]" class="form-control" placeholder="Warna lain..." required></td>
            <td><input type="number" name="s[]" class="form-control text-center size-input" value="0" min="0"></td>
            <td><input type="number" name="m[]" class="form-control text-center size-input" value="0" min="0"></td>
            <td><input type="number" name="l[]" class="form-control text-center size-input" value="0" min="0"></td>
            <td><input type="number" name="xl[]" class="form-control text-center size-input" value="0" min="0"></td>
            <td class="text-center fw-bold row-total">0</td>
            <td><button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-times"></i></button></td>
        </tr>`;
        tableBody.insertAdjacentHTML('beforeend', row);
    });

    tableBody.addEventListener('input', e => {
        if(e.target.classList.contains('size-input')) calculate();
    });

    tableBody.addEventListener('click', e => {
        if(e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            calculate();
        }
    });

    calculate(); // Hitung pas pertama kali buka
</script>
@endsection