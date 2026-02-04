<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    // Kita cek dulu, kalau kolom BELUM ada, baru kita buat
    if (!Schema::hasColumn('orders', 'qty_finishing')) {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('qty_finishing')->default(0)->after('qty_produksi');
        });
    }
}

public function down()
{
    if (Schema::hasColumn('orders', 'qty_finishing')) {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qty_finishing');
        });
    }
}
};