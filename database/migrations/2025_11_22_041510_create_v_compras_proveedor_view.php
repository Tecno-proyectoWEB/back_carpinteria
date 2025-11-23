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
        DB::statement("CREATE VIEW \"v_compras_proveedor\" AS SELECT pr.id AS proveedor_id,
    pr.nombre AS proveedor,
    count(c.id) AS total_compras,
    sum(c.importe_total) AS monto_total,
    max(c.fecha) AS ultima_compra
   FROM (proveedor pr
     LEFT JOIN compra c ON (((pr.id = c.proveedor_id) AND ((c.estado)::text = 'COMPLETADA'::text))))
  GROUP BY pr.id, pr.nombre
  ORDER BY (sum(c.importe_total)) DESC NULLS LAST;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_compras_proveedor\"");
    }
};
