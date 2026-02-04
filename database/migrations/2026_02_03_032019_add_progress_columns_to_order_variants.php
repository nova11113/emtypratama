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
    Schema::table('order_variants', function (Blueprint $table) {
        // Kolom progress per warna
        $table->integer('cutting_s')->default(0); $table->integer('cutting_m')->default(0);
        $table->integer('cutting_l')->default(0); $table->integer('cutting_xl')->default(0);
        
        $table->integer('sewing_s')->default(0); $table->integer('sewing_m')->default(0);
        $table->integer('sewing_l')->default(0); $table->integer('sewing_xl')->default(0);
        
        $table->integer('finishing_s')->default(0); $table->integer('finishing_m')->default(0);
        $table->integer('finishing_l')->default(0); $table->integer('finishing_xl')->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('order_variants', function (Blueprint $table) {
        $table->dropColumn([
            'cutting_s', 'cutting_m', 'cutting_l', 'cutting_xl',
            'sewing_s', 'sewing_m', 'sewing_l', 'sewing_xl',
            'finishing_s', 'finishing_m', 'finishing_l', 'finishing_xl'
        ]);
    });
}
};
