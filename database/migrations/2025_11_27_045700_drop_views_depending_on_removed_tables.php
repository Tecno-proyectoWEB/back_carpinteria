<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Elimina vistas que dependen de tablas eliminadas (compra, proveedor, bitacora)
     */
    public function up(): void
    {
        // Eliminar vistas que dependen de tablas eliminadas
        DB::statement("DROP VIEW IF EXISTS v_compras_proveedor");
        DB::statement("DROP VIEW IF EXISTS v_resumen_bitacora");
        DB::statement("DROP VIEW IF EXISTS v_actividad_usuarios");
    }

    /**
     * Reverse the migrations.
     * Nota: Estas vistas no se pueden recrear porque las tablas fueron eliminadas
     */
    public function down(): void
    {
        // No se pueden recrear porque las tablas compra, proveedor y bitacora fueron eliminadas
    }
};
