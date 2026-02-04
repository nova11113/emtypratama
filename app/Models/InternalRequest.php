<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalRequest extends Model
{
    use HasFactory;

    // Tambahin baris ini supaya Laravel bolehin simpen data lewat ::create
    protected $fillable = [
    'divisi', 
    'pesan', 
    'is_read'
];

// Tambahin ini biar Laravel otomatis ngerubah 0/1 jadi true/false
protected $casts = [
    'is_read' => 'boolean',
];
}