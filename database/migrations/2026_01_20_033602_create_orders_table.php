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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_order');
            $table->string('customer');
            $table->string('produk'); 
            $table->integer('jumlah');
            
            // Kolom tracking pcs
            $table->integer('qty_order')->default(0);    
            $table->integer('qty_produksi')->default(0); 
            $table->integer('qty_finishing')->default(0);
            $table->integer('qty_qc')->default(0);       
            $table->float('progress')->default(0);       

            // Status enum lengkap
            $table->enum('status', ['order', 'produksi', 'finishing', 'qc', 'selesai'])->default('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};