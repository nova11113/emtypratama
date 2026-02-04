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
        Schema::table('order_variants', function (Blueprint $table) {
            $steps = ['cutting', 'sewing', 'finishing', 'qc', 'qc_reject'];
            $sizes = ['s', 'm', 'l', 'xl'];
            
            foreach ($steps as $step) {
                foreach ($sizes as $size) {
                    $column = $step . '_' . $size;
                    // Cek biar nggak duplikat kalau lu migrate ulang
                    if (!Schema::hasColumn('order_variants', $column)) {
                        $table->integer($column)->default(0)->after('total');
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_variants', function (Blueprint $table) {
            $steps = ['cutting', 'sewing', 'finishing', 'qc', 'qc_reject'];
            $sizes = ['s', 'm', 'l', 'xl'];
            
            foreach ($steps as $step) {
                foreach ($sizes as $size) {
                    $column = $step . '_' . $size;
                    if (Schema::hasColumn('order_variants', $column)) {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};