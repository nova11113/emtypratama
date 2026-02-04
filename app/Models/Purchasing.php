<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchasing extends Model
{
    use HasFactory;

    // Masukkan kolom yang boleh diisi (Mass Assignment)
    protected $fillable = [
        'material_id', 
        'supplier', 
        'jumlah', 
        'satuan', 
        'tgl_estimasi', 
        'status'
    ];

    // Relasi ke tabel Material (Bahan Baku)
    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}