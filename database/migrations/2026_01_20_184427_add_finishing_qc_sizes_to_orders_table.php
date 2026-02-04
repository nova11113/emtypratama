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
        // Kolom Finishing per size
        $table->integer('finishing_s')->default(0);
        $table->integer('finishing_m')->default(0);
        $table->integer('finishing_l')->default(0);
        $table->integer('finishing_xl')->default(0);
        
        // Kolom QC per size
        $table->integer('qc_s')->default(0);
        $table->integer('qc_m')->default(0);
        $table->integer('qc_l')->default(0);
        $table->integer('qc_xl')->default(0);
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
