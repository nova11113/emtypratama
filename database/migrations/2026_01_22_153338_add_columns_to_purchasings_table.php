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
    Schema::table('purchasings', function (Blueprint $table) {
        // Tambahkan kolom yang kurang
        $table->foreignId('material_id')->after('id')->constrained('materials')->onDelete('cascade');
        $table->string('supplier')->after('material_id');
        $table->integer('jumlah')->after('supplier');
        $table->string('satuan')->after('jumlah');
        $table->string('status')->default('pending')->after('satuan');
    });
}

public function down(): void
{
    Schema::table('purchasings', function (Blueprint $table) {
        $table->dropForeign(['material_id']);
        $table->dropColumn(['material_id', 'supplier', 'jumlah', 'satuan', 'status']);
    });
}
};
