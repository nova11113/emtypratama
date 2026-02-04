<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        Employee::create([
            'nama'     => 'Admin Konveksi',
            'email'    => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'jabatan'  => 'admin', // Sesuaikan dengan Screenshot_747
            'no_hp'    => '08123456789',
            'aktif'    => 1
        ]);
    }
}