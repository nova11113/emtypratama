<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $shipment->order->kode_order }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .no-print { margin-bottom: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 10px; text-align: center; }
        .data-table th { background-color: #f2f2f2; }
        .footer-table { width: 100%; margin-top: 40px; }
        .footer-table td { text-align: center; width: 33%; }
        .space { height: 70px; }
        @media print { .no-print { display: none; } body { padding: 0; } }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer; font-weight: bold;">🖨️ CETAK SURAT JALAN</button>
        <a href="{{ route('gudang.index') }}" style="margin-left: 10px; text-decoration: none; color: #666;">← Kembali ke Gudang</a>
    </div>

    <div class="header">
        <h1 style="margin: 0; font-size: 20px;">SURAT JALAN PENGIRIMAN</h1>
        <p style="margin: 5px 0;">No. Surat: {{ $shipment->no_surat_jalan }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%">Customer</td><td width="35%">: <b>{{ $shipment->order->customer }}</b></td>
            <td width="15%">Tanggal</td><td width="35%">: {{ $shipment->created_at->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Kode PO</td><td>: {{ $shipment->order->kode_order }}</td>
            <td>Produk</td><td>: {{ $shipment->order->produk }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Size S</th>
                <th>Size M</th>
                <th>Size L</th>
                <th>Size XL</th>
                <th style="background: #333; color: #fff;">TOTAL QTY</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $shipment->s }}</td>
                <td>{{ $shipment->m }}</td>
                <td>{{ $shipment->l }}</td>
                <td>{{ $shipment->xl }}</td>
                <td style="font-weight: bold; font-size: 14px;">{{ $shipment->total }} Pcs</td>
            </tr>
        </tbody>
    </table>

    <table class="footer-table">
        <tr>
            <td>Penerima,</td>
            <td>Sopir / Kurir,</td>
            <td>Hormat Kami,</td>
        </tr>
        <tr class="space">
            <td></td><td></td><td></td>
        </tr>
        <tr>
            <td>( ____________________ )</td>
            <td>( ____________________ )</td>
            <td>( Bagian Gudang )</td>
        </tr>
    </table>

</body>
</html>