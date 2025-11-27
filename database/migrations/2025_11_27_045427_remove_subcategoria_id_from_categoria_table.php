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
        Schema::table('categoria', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['subcategoria_id']);
            // Eliminar índice
            $table->dropIndex('idx_categoria_subcategoria');
            // Eliminar columna
            $table->dropColumn('subcategoria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categoria', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategoria_id')->nullable()->index('idx_categoria_subcategoria');
            $table->foreign('subcategoria_id')->references('id')->on('subcategoria')->onDelete('set null');
        });
    }
};
