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
        // Tambahin kolom image setelah kolom produk
        $table->string('image')->nullable()->after('produk');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        // Buat jaga-jaga kalau di-rollback, kolomnya dihapus
        $table->dropColumn('image');
    });
}
};
