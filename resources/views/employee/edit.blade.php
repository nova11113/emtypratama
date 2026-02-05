@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 text-center">
                    <h3 class="fw-bold text-dark">📝 Edit Data Karyawan</h3>
                    <p class="text-muted small">Perbarui informasi tim Garment Emty</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="POST" action="/karyawan/{{ $employee->id }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input name="nama" type="text" class="form-control" value="{{ $employee->nama }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Jabatan / Divisi</label>
                            <select name="jabatan" class="form-select" required>
                                <option value="Cutting" {{ $employee->jabatan == 'Cutting' ? 'selected' : '' }}>Divisi Cutting (Potong)</option>
                                <option value="Sewing" {{ $employee->jabatan == 'Sewing' ? 'selected' : '' }}>Divisi Sewing (Jahit)</option>
                                <option value="Finishing" {{ $employee->jabatan == 'Finishing' ? 'selected' : '' }}>Divisi Finishing</option>
                                <option value="QC" {{ $employee->jabatan == 'QC' ? 'selected' : '' }}>Divisi Quality Control</option>
                                <option value="Admin" {{ $employee->jabatan == 'Admin' ? 'selected' : '' }}>Admin / Kantor</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Nomor HP (WhatsApp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">62</span>
                                <input name="no_hp" type="number" class="form-control" value="{{ $employee->no_hp }}" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold py-2 shadow-sm">
                                Simpan Perubahan
                            </button>
                            <a href="/karyawan" class="btn btn-light fw-bold py-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
