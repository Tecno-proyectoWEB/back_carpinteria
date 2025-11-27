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
        Schema::table('movimiento_inventario', function (Blueprint $table) {
            if (Schema::hasColumn('movimiento_inventario', 'compra_id')) {
                $table->dropForeign(['compra_id']);
                $table->dropColumn('compra_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('compra_id')->nullable()->after('usuario_id');
            $table->index('compra_id', 'idx_movimiento_compra');
            $table->foreign('compra_id')->references('id')->on('compra')->onDelete('set null');
        });
    }
};

