<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanLog extends Model
{
    // Nama tabel di database
    protected $table = 'bahan_logs';

    // Kolom yang boleh diisi manual
    protected $fillable = [
        'bahan_id', 
        'aksi', 
        'jumlah', 
        'saldo_akhir', 
        'keterangan', 
        'operator'
    ];

    // Relasi: Biar kita tau nama bahannya pas liat log
    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }
}