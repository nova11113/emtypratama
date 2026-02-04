<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderVariant extends Model
{
    protected $table = 'order_variants';

    protected $fillable = [
        'order_id', 'warna', 's', 'm', 'l', 'xl', 'total',
        // Tambahin kolom progress ini bro:
        'cutting_s', 'cutting_m', 'cutting_l', 'cutting_xl',
        'sewing_s', 'sewing_m', 'sewing_l', 'sewing_xl',
        'finishing_s', 'finishing_m', 'finishing_l', 'finishing_xl'
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }
}