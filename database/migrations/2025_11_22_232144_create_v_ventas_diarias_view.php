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
        DB::statement("CREATE VIEW \"v_ventas_diarias\" AS SELECT date(v.fecha) AS fecha,
    count(*) AS total_ventas,
    sum(v.importe_total) AS monto_total,
    avg(v.importe_total) AS promedio_venta,
    mp.nombre AS metodo_pago
   FROM (venta v
     JOIN metodo_pago mp ON ((v.metodo_pago_id = mp.id)))
  WHERE (v.estado = true)
  GROUP BY (date(v.fecha)), mp.nombre
  ORDER BY (date(v.fecha)) DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_ventas_diarias\"");
    }
};

