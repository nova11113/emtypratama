<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kode ini bakal bikin akun Admin biar lo bisa login
        User::factory()->create([
            'name' => 'AGUNG',
            'email' => 'agung@emty.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
