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
        Schema::create('daily_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained();
    $table->string('divisi'); // cutting, sewing, finishing, qc
    $table->string('size');   // s, m, l, xl
    $table->integer('qty');   // jumlah yang dikerjakan hari ini
    $table->date('tanggal');  // tanggal pengerjaan
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
