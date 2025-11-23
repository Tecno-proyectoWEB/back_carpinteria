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
        DB::statement("CREATE VIEW \"v_ventas_diarias\" AS SELECT date(p.fecha) AS fecha,
    count(*) AS total_pedidos,
    sum(p.importe_total) AS monto_total,
    avg(p.importe_total) AS promedio_venta,
    mp.nombre AS metodo_pago
   FROM (pedido p
     JOIN metodo_pago mp ON ((p.metodo_pago_id = mp.id)))
  WHERE (p.estado = true)
  GROUP BY (date(p.fecha)), mp.nombre
  ORDER BY (date(p.fecha)) DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_ventas_diarias\"");
    }
};
