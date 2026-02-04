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
    Schema::create('material_requests', function (Blueprint $table) {
        $table->id();
        $table->string('kode_request')->unique();
        
        // GANTI BAGIAN INI: dari 'inventories' jadi 'materials'
        $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
        
        $table->decimal('qty_minta', 15, 2);
        $table->string('keperluan');
        $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
        $table->foreignId('user_id')->constrained('users');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
