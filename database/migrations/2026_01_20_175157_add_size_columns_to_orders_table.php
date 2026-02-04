<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        // Tambahkan kolom size setelah kolom jumlah
        $table->integer('size_s')->default(0)->after('jumlah');
        $table->integer('size_m')->default(0)->after('size_s');
        $table->integer('size_l')->default(0)->after('size_m');
        $table->integer('size_xl')->default(0)->after('size_l');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
