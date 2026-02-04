@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 text-start">
    {{-- HEADER & NOTIFIKASI --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">🏠 Monitoring Produksi Real-Time</h2>
        <div class="dropdown">
            <button class="btn btn-outline-dark position-relative" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                @if(($notif_chat ?? 0) > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $notif_chat }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-3" style="width: 300px;">
                <h6 class="fw-bold border-bottom pb-2">Pesan Divisi Terbaru</h6>
                @forelse($pesan_terbaru ?? [] as $p)
                <div class="mb-3 border-bottom pb-2 d-flex justify-content-between align-items-start">
                    <div style="flex: 1;">
                        <span class="badge bg-warning text-dark">{{ $p->divisi }}</span>
                        <p class="small m-0 text-dark">{{ $p->pesan }}</p>
                        <small class="text-muted">{{ $p->created_at->diffForHumans() }}</small>
                    </div>
                    <form action="{{ route('internal.baca', $p->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success border-0">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    </form>
                </div>
                @empty
                    <p class="small text-muted m-0 p-2 text-center">Tidak ada pesan baru.</p>
                @endforelse
                <a href="{{ route('internal.history') }}" class="btn btn-sm btn-primary w-100 mt-2">Lihat Semua</a>
            </div>
        </div>
    </div>
    
    {{-- KARTU STATISTIK ATAS --}}
    <div class="row g-3 mb-4 text-center">
        <div class="col-md-3">
            <div class="card bg-primary text-white p-3 shadow-sm border-0 h-100">
                <small>Total Target Order</small>
                <h3 class="fw-bold mb-0">{{ number_format($total_order ?? 0) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white p-3 shadow-sm border-0 h-100">
                <small>Barang Jadi (QC Pass)</small>
                <h3 class="fw-bold mb-0">{{ number_format($total_qc ?? 0) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark p-3 shadow-sm border-0 h-100">
                <small>Stok Siap Kirim</small>
                <h3 class="fw-bold mb-0">{{ number_format($total_gudang ?? 0) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white p-3 shadow-sm border-0 h-100">
                <small>Pesan Butuh Bantuan</small>
                <h3 class="fw-bold mb-0">{{ $notif_chat ?? 0 }}</h3>
            </div>
        </div>
    </div>

    {{-- TABEL PROGRESS PO UTAMA --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 fw-bold text-primary border-bottom">
            Klik Kode PO untuk Lihat Rincian Progress
        </div>
        <div class="card-body p-0 text-center">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 text-start">Kode PO</th>
                            <th>Produk</th>
                            <th>Target PO</th> 
                            <th>Status</th>
                            <th>Progress Produksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders ?? [] as $o)
                        <tr>
                            <td class="ps-4 text-start text-dark fw-bold">
                                <a href="javascript:void(0)" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#detailModal{{ $o->id }}">
                                    {{ $o->kode_order }}
                                </a>
                            </td>
                            <td>{{ $o->customer }} - {{ $o->produk }}</td>
                            <td class="fw-bold">{{ number_format($o->jumlah) }} pcs</td> 
                            <td>
                                <span class="badge bg-{{ $o->status == 'selesai' ? 'success' : 'info text-dark' }}">
                                    {{ strtoupper($o->status) }}
                                </span>
                            </td>
                            <td width="250">
                                <div class="progress" style="height: 12px; border-radius: 10px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: {{ $o->progress }}%"></div>
                                </div>
                                <small class="fw-bold text-muted">{{ round($o->progress) }}% Selesai</small>
                            </td>
                        </tr>

                        {{-- MODAL DETAIL PO --}}
                        <div class="modal fade" id="detailModal{{ $o->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <div class="modal-header bg-dark text-white border-0">
                                        <h5 class="modal-title">Rincian: {{ $o->kode_order }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-start">
                                        <div class="mb-3 p-2 bg-primary-subtle rounded text-center text-dark">
                                            <strong>🎯 Target Pesanan: {{ number_format($o->jumlah) }} pcs</strong>
                                        </div>
                                        <div class="row g-3 text-center mb-3">
                                            <div class="col-6 border rounded p-2 text-dark"><small>✂️ Cutting</small><br><strong>{{ $o->qty_cutting }}</strong></div>
                                            <div class="col-6 border rounded p-2 text-dark"><small>🪡 Sewing</small><br><strong>{{ $o->qty_produksi }}</strong></div>
                                            <div class="col-6 border rounded p-2 text-dark"><small>✨ Finishing</small><br><strong>{{ $o->qty_finishing }}</strong></div>
                                            <div class="col-6 border rounded bg-success-subtle p-2 text-dark"><small>🔍 QC Pass</small><br><strong>{{ $o->qty_qc }}</strong></div>
                                        </div>
                                        <div class="p-2 bg-danger-subtle text-danger rounded d-flex justify-content-between">
                                            <span>❌ Reject:</span><strong>{{ $o->qty_reject }} pcs</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-white p-3">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                📊 Ringkasan Output {{ \Carbon\Carbon::parse(request('tanggal', date('Y-m-d')))->format('d M Y') }}
            </h6>
            <div class="row text-center">
                @php
                    $divisi_list = [
                        'cutting' => ['label' => 'Potong (Cutting)', 'icon' => '✂️', 'color' => 'info'],
                        'produksi' => ['label' => 'Jahit (Sewing)', 'icon' => '🪡', 'color' => 'primary'],
                        'finishing' => ['label' => 'Finishing', 'icon' => '✨', 'color' => 'warning'],
                        'qc' => ['label' => 'QC Pass', 'icon' => '🔍', 'color' => 'success'],
                    ];
                @endphp

                @foreach($divisi_list as $key => $div)
                <div class="col-md-3 border-end">
                    <small class="text-muted d-block">{{ $div['icon'] }} {{ $div['label'] }}</small>
                    <h4 class="fw-bold text-{{ $div['color'] }} mb-0">
                        {{ number_format($dailyReports->where('divisi', $key)->sum('qty')) }}
                    </h4>
                    <small class="text-muted" style="font-size: 10px;">Pcs</small>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
    {{-- TABEL SETORAN HARIAN DENGAN FILTER & REKAP --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center">
                <h6 class="m-0 fw-bold text-primary me-3">
                    <i class="fas fa-history me-2"></i>Setoran Produksi
                </h6>
                <span class="badge bg-primary rounded-pill">{{ count($dailyReports ?? []) }} Aktivitas</span>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="fas fa-calendar-alt text-muted"></i>
                    </span>
                    <input type="date" name="tanggal" class="form-control form-control-sm border-start-0 ps-0 text-dark" 
                           style="width: 150px;"
                           value="{{ request('tanggal', date('Y-m-d')) }}" 
                           onchange="this.form.submit()"> 
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="100">Jam</th>
                            <th class="text-start">PO / Produk</th>
                            <th>Divisi</th>
                            <th>Size</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyReports ?? [] as $log)
                        @php
                            $color = [
                                'cutting' => 'info text-dark',
                                'produksi' => 'primary',
                                'finishing' => 'warning text-dark',
                                'qc' => 'success'
                            ][$log->divisi] ?? 'secondary';
                        @endphp
                        <tr style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalLog{{ $log->id }}">
                            <td class="text-muted small">
                                <i class="far fa-clock me-1"></i>{{ $log->created_at->format('H:i') }}
                            </td>
                            <td class="text-start">
                                <span class="fw-bold text-dark">{{ $log->order->kode_order ?? 'N/A' }}</span><br>
                                <small class="text-muted">{{ $log->order->produk ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $color }} text-uppercase" style="font-size: 11px;">
                                    {{ $log->divisi == 'produksi' ? 'SEWING' : $log->divisi }}
                                </span>
                            </td>
                            <td>
                                <span class="badge border text-dark bg-light">{{ strtoupper($log->size) }}</span>
                            </td>
                            <td class="fw-bold text-dark">
                                <span class="text-success">+{{ number_format($log->qty) }}</span> <small>Pcs</small>
                            </td>
                        </tr>

                        {{-- MODAL DETAIL LOG SETORAN --}}
                        <div class="modal fade" id="modalLog{{ $log->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content border-0 shadow-lg text-dark">
                                    <div class="modal-header border-0 pb-0">
                                        <h6 class="modal-title fw-bold">Rincian Setoran</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center py-4">
                                        <div class="mb-3">
                                            <span class="display-6 fw-bold text-success">{{ number_format($log->qty) }}</span>
                                            <span class="text-muted">Pcs</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Size:</span><strong>{{ strtoupper($log->size) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Divisi:</span><strong>{{ strtoupper($log->divisi) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Waktu:</span><strong>{{ $log->created_at->format('H:i:s') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="py-5 text-muted text-center">
                                Tidak ada setoran pada tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                    @if(count($dailyReports ?? []) > 0)
                    <tfoot class="table-light fw-bold border-top sticky-bottom">
                        <tr class="bg-light-subtle">
                            <td colspan="4" class="text-start ps-4 py-3">
                                <div class="d-flex gap-2 flex-wrap align-items-center">
                                    <small class="text-muted text-uppercase fw-bold me-2" style="font-size: 10px;">Total Per Size:</small>
                                    @foreach($dailyReports->groupBy('size') as $size => $items)
                                        <div class="badge border text-dark bg-white shadow-sm py-2 px-3">
                                            {{ strtoupper($size) }}: <span class="text-primary">{{ number_format($items->sum('qty')) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-success py-3 text-center" style="font-size: 1.1rem;">
                                TOTAL: +{{ number_format($dailyReports->sum('qty')) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@endsection