<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("CREATE VIEW \"v_stock_bajo_materiales\" AS SELECT 'MATERIAL'::text AS tipo,
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
  ORDER BY m.stock_actual;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_stock_bajo_materiales\"");
    }
};
