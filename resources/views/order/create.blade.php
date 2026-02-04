@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">📝 Buat Order Baru</h2>
        <a href="{{ url('/order') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow border-0" style="border-radius: 15px;">
                <div class="card-body p-4">
                    {{-- WAJIB: Tambahin enctype biar bisa upload file --}}
                    <form action="{{ url('/order/store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Pelanggan / Customer</label>
                            <input type="text" name="customer" class="form-control form-control-lg" placeholder="Contoh: Toko Abadi" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Produk</label>
                            <input type="text" name="produk" class="form-control form-control-lg" placeholder="Contoh: Celana Jeans" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Jumlah Order (Pcs)</label>
                                <input type="number" name="jumlah" class="form-control form-control-lg" placeholder="Misal: 500" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Upload Model / Contoh Produk</label>
                                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, WebP (Maks. 2MB)</small>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg shadow">
                                <i class="fas fa-save me-1"></i> Simpan Order Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- BAGIAN PREVIEW GAMBAR (BARU) --}}
        <div class="col-md-5">
            <div class="card shadow border-0 text-center p-4" style="border-radius: 15px; min-height: 300px;">
                <h6 class="text-muted mb-3">Preview Model Produk</h6>
                <div id="previewContainer" class="d-flex align-items-center justify-content-center border rounded" style="min-height: 250px; background: #f8f9fa;">
                    <img id="imgPreview" src="#" alt="Preview" class="img-fluid d-none" style="max-height: 250px; border-radius: 10px;">
                    <div id="placeholderText">
                        <i class="fas fa-images fa-4x text-light mb-2"></i>
                        <p class="text-muted">Belum ada foto dipilih</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT LIVE PREVIEW --}}
<script>
    imageInput.onchange = evt => {
        const [file] = imageInput.files
        if (file) {
            imgPreview.src = URL.createObjectURL(file)
            imgPreview.classList.remove('d-none')
            document.getElementById('placeholderText').classList.add('d-none')
        }
    }
</script>
@endsection