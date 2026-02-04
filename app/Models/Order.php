<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
    'kode_order', 'customer', 'produk', 'jumlah',
    'image', 'size_chart',
    'size_s', 'size_m', 'size_l', 'size_xl', 
    
    // TAMBAHIN INI BRO (KOLOM CUTTING):
    'cutting_s', 'cutting_m', 'cutting_l', 'cutting_xl',
    
    'sewing_s', 'sewing_m', 'sewing_l', 'sewing_xl',
    'qty_order', 'qty_produksi', 'qty_finishing', 'qty_qc', 'progress', 'status',
    
    // TAMBAHIN JUGA REKAP TOTAL CUTTING:
    'qty_cutting' 
];

    protected $casts = [
        'jumlah' => 'integer',
        'qty_order' => 'integer',
        'qty_produksi' => 'integer',
        'qty_finishing' => 'integer',
        'qty_qc' => 'integer',
        'progress' => 'float',
    ];

    // --- TAMBAHKAN RELASI INI DI SINI ---
    public function productionLogs(): HasMany
    {
        // Parameter kedua 'order_id' adalah kolom di tabel production_logs
        // Parameter ketiga 'id' adalah kolom primary key di tabel orders
        return $this->hasMany(ProductionLog::class, 'order_id', 'id');
    }
    public function variants()
{
    return $this->hasMany(OrderVariant::class);
}
    // ------------------------------------
}