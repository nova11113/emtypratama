<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    // Tambahkan baris ini bro untuk kasih izin kolom mana saja yang boleh diisi
    protected $fillable = [
        'nama_bahan',
        'kategori',
        'stok',
        'satuan',
        'minimal_stok'
    ];
}