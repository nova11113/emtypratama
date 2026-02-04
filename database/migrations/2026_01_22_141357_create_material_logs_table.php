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
        Schema::create('material_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('material_id')->constrained();
    $table->enum('tipe', ['masuk', 'keluar']);
    $table->integer('jumlah');
    $table->string('keterangan'); // Misal: "Pembelian dari Supplier A" atau "Dipakai Order ORD-123"
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_logs');
    }
};
