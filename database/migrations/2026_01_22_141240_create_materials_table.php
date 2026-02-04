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
        Schema::create('materials', function (Blueprint $table) {
    $table->id();
    $table->string('nama_bahan'); // Misal: Combed 30s Hitam
    $table->string('satuan');     // Misal: Kg, Roll, Yard
    $table->integer('stok')->default(0);
    $table->integer('minimal_stok')->default(5); // Notifikasi kalau mau habis
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
