<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kita hanya tambah kolom 'qty_finishing' karena ini yang belum ada
            if (!Schema::hasColumn('orders', 'qty_finishing')) {
                $table->integer('qty_finishing')->default(0)->after('qty_produksi');
            }

            // Pastikan kolom progress ada untuk menghitung % penyelesaian
            if (!Schema::hasColumn('orders', 'progress')) {
                $table->float('progress')->default(0)->after('qty_qc');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['qty_finishing', 'progress']);
        });
    }
};