<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar la vista que depende de sector_id
        DB::statement("DROP VIEW IF EXISTS v_stock_bajo_materiales");

        Schema::table('material', function (Blueprint $table) {
            // Eliminar foreign key primero
            $table->dropForeign(['sector_id']);
            // Eliminar índice
            $table->dropIndex('idx_material_sector');
            // Eliminar columna
            $table->dropColumn('sector_id');
        });

        // Recrear la vista sin la referencia a sector
        DB::statement("CREATE VIEW v_stock_bajo_materiales AS
            SELECT
                'MATERIAL'::text AS tipo,
                m.id,
                m.nombre,
                m.stock_actual,
                m.stock_minimo,
                m.punto_reorden,
                (m.stock_minimo - m.stock_actual) AS faltante,
                CASE
                    WHEN (m.stock_actual <= 0) THEN 'CRITICO'::text
                    WHEN (m.stock_actual <= m.punto_reorden) THEN 'REORDENAR'::text
                    ELSE 'BAJO'::text
                END AS estado_alerta
            FROM material m
            WHERE ((m.stock_actual <= m.stock_minimo) AND (m.activo = true))
            ORDER BY m.stock_actual");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la vista actualizada
        DB::statement("DROP VIEW IF EXISTS v_stock_bajo_materiales");

        Schema::table('material', function (Blueprint $table) {
            $table->unsignedBigInteger('sector_id')->nullable()->index('idx_material_sector');
            $table->foreign('sector_id')->references('id')->on('sector')->onDelete('set null');
        });

        // Recrear la vista original con sector
        DB::statement("CREATE VIEW v_stock_bajo_materiales AS
            SELECT
                'MATERIAL'::text AS tipo,
                m.id,
                m.nombre,
                m.stock_actual,
                m.stock_minimo,
                m.punto_reorden,
                (m.stock_minimo - m.stock_actual) AS faltante,
                CASE
                    WHEN (m.stock_actual <= 0) THEN 'CRITICO'::text
                    WHEN (m.stock_actual <= m.punto_reorden) THEN 'REORDENAR'::text
                    ELSE 'BAJO'::text
                END AS estado_alerta,
                s.nombre AS sector,
                a.nombre AS almacen
            FROM ((material m
                LEFT JOIN sector s ON ((m.sector_id = s.id)))
                LEFT JOIN almacen a ON ((s.almacen_id = a.id)))
            WHERE ((m.stock_actual <= m.stock_minimo) AND (m.activo = true))
            ORDER BY m.stock_actual");
    }
};
