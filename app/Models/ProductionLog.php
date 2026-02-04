<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLog extends Model
{
    // 1. Daftarkan kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'order_id', 
        'divisi', 
        'size', 
        'qty', 
        'tanggal'
    ];

    // 2. Buat relasi ke model Order
    // Supaya di Dashboard bisa panggil $log->order->kode_order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}