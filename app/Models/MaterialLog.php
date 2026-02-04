<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialLog extends Model
{
    // Nama tabel di database sesuai screenshot lu
    protected $table = 'material_logs'; 

    protected $fillable = [
        'material_id', // Bukan bahan_id
        'tipe',        // Bukan aksi
        'jumlah', 
        'keterangan'
    ];

    public function material() {
        return $this->belongsTo(Material::class, 'material_id');
    }
}