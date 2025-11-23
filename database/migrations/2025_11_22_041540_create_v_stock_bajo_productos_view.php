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
        DB::statement("CREATE VIEW \"v_stock_bajo_productos\" AS SELECT 'PRODUCTO'::text AS tipo,
    p.id,
    p.nombre,
    p.stock AS stock_actual,
    p.stock_minimo,
    (p.stock_minimo - p.stock) AS faltante,
    c.nombre AS categoria
   FROM (producto p
     LEFT JOIN categoria c ON ((p.categoria_id = c.id)))
  WHERE (p.stock <= p.stock_minimo)
  ORDER BY (p.stock_minimo - p.stock) DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_stock_bajo_productos\"");
    }
};
