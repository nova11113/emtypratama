<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    // Tambahkan properti fillable agar mass assignment diperbolehkan
    protected $fillable = [
        'order_id', 
        'no_surat_jalan', 
        's', 
        'm', 
        'l', 
        'xl', 
        'total',
        'ekspedisi',
    ];

    /**
     * Relasi ke model Order (Satu pengiriman milik satu Order)
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}