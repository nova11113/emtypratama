<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    use HasFactory;

    // Tambahin kolom-kolom ini biar bisa disave sekaligus
    protected $fillable = [
        'kode_request', 
        'material_id', 
        'qty_minta', 
        'keperluan', 
        'status', 
        'user_id'
    ];

    public function material() {
        return $this->belongsTo(Material::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}