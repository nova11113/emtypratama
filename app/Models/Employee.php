<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable 
{
    use Notifiable;

    protected $table = 'employees';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'jabatan',
        'no_hp',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}