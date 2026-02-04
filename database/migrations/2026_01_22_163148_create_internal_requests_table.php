<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('internal_requests', function (Blueprint $table) {
        $table->id();
        $table->string('divisi'); // Cutting, Sewing, dll
        $table->text('pesan');
        $table->boolean('is_read')->default(false); // Buat notif
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_requests');
    }
};
