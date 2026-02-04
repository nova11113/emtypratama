<!DOCTYPE html>
<html>
<head>
    <title>Surat Jalan - {{ $shipment->no_surat_jalan }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0; font-size: 14px; font-weight: bold; }
        
        .info-table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        .content-table th { background-color: #f2f2f2; border: 1px solid #000; padding: 10px; text-align: center; text-transform: uppercase; }
        .content-table td { border: 1px solid #000; padding: 10px; text-align: center; font-size: 13px; }
        
        .footer { margin-top: 60px; width: 100%; }
        .sig-container { display: table; width: 100%; }
        .sig { display: table-cell; width: 33%; text-align: center; vertical-align: top; }
        .sig-space { height: 80px; }
        
        @media print {
            body { margin: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>SURAT JALAN / DELIVERY ORDER</h1>
        <p>EMTY GARMENT PRODUCTION</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Nomor SJ</td><td width="35%">: <b>{{ $shipment->no_surat_jalan }}</b></td>
            <td width="15%">Tanggal</td><td width="35%">: {{ date('d/m/Y', strtotime($shipment->created_at)) }}</td>
        </tr>
        <tr>
            <td>Nomor PO</td><td>: {{ $order->kode_order }}</td>
            <td>Ekspedisi</td><td>: {{ $shipment->ekspedisi }}</td>
        </tr>
        <tr>
            <td>Customer</td><td>: {{ $order->customer }}</td>
            <td>Produk</td><td>: {{ $order->produk }}</td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th rowspan="2" style="vertical-align: middle;">Nama Barang / Produk</th>
                <th colspan="4">Rincian Size</th>
                <th rowspan="2" style="vertical-align: middle;">Total (Pcs)</th>
            </tr>
            <tr>
                <th width="10%">S</th>
                <th width="10%">M</th>
                <th width="10%">L</th>
                <th width="10%">XL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;">{{ $order->produk }}</td>
                {{-- Data size diambil dari tabel shipment --}}
                <td>{{ $shipment->s ?? 0 }}</td>
                <td>{{ $shipment->m ?? 0 }}</td>
                <td>{{ $shipment->l ?? 0 }}</td>
                <td>{{ $shipment->xl ?? 0 }}</td>
                <td style="font-weight: bold; font-size: 15px;">{{ $shipment->total }}</td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top: 20px;"><i>Keterangan: Barang telah diperiksa dan diterima dalam kondisi baik.</i></p>

    <div class="footer">
        <div class="sig-container">
            <div class="sig">
                Diterima Oleh,<br>
                <div class="sig-space"></div>
                ( ............................ )<br>
                <small>Stempel & Nama Terang</small>
            </div>
            <div class="sig">
                Supir / Ekspedisi,<br>
                <div class="sig-space"></div>
                ( ............................ )<br>
                <small>Nama Terang</small>
            </div>
            <div class="sig">
                Hormat Kami,<br>
                <div class="sig-space"></div>
                ( <b>Bagian Gudang</b> )<br>
                <small>EMTY GARMENT</small>
            </div>
        </div>
    </div>
</body>
</html>