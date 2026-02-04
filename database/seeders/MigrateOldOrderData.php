<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderVariant;

class MigrateOldOrderData extends Seeder
{
    public function run()
    {
        $orders = Order::all();

        foreach ($orders as $o) {
            // Cek kalau rincian warnanya beneran masih kosong
            if ($o->variants()->count() == 0) {
                OrderVariant::create([
                    'order_id' => $o->id,
                    'warna'    => 'Default/Putih', // Kasih nama sementara
                    's'        => $o->size_s ?? 0,
                    'm'        => $o->size_m ?? 0,
                    'l'        => $o->size_l ?? 0,
                    'xl'       => $o->size_xl ?? 0,
                    'total'    => ($o->size_s + $o->size_m + $o->size_l + $o->size_xl)
                ]);
            }
        }
        
        $this->command->info('Mantap bro! Data lama sudah dipindah ke tabel warna.');
    }
}