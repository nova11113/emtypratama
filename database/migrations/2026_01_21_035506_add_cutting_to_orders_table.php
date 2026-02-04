<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Menambah kolom target cutting per size setelah size_xl
            $table->integer('cutting_s')->default(0)->after('size_xl');
            $table->integer('cutting_m')->default(0)->after('cutting_s');
            $table->integer('cutting_l')->default(0)->after('cutting_m');
            $table->integer('cutting_xl')->default(0)->after('cutting_l');

            // Menambah total akumulasi cutting setelah kolom jumlah
            $table->integer('qty_cutting')->default(0)->after('jumlah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['qty_cutting', 'cutting_s', 'cutting_m', 'cutting_l', 'cutting_xl']);
        });
    }
};