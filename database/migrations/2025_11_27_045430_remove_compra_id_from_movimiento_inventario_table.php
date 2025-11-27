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
            // Eliminar foreign key primero
            $table->dropForeign(['compra_id']);
            // Eliminar índice
            $table->dropIndex('idx_movimiento_compra');
            // Eliminar columna
            $table->dropColumn('compra_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimiento_inventario', function (Blueprint $table) {
            $table->unsignedBigInteger('compra_id')->nullable()->index('idx_movimiento_compra');
            $table->foreign('compra_id')->references('id')->on('compra')->onDelete('set null');
        });
    }
};
