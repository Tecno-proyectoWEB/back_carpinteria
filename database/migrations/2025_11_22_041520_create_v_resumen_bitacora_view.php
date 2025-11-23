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
        DB::statement("CREATE VIEW \"v_resumen_bitacora\" AS SELECT modulo,
    accion,
    count(*) AS total,
    max(fecha) AS ultima_vez
   FROM bitacora
  GROUP BY modulo, accion
  ORDER BY modulo, (count(*)) DESC;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_resumen_bitacora\"");
    }
};
