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
        DB::statement("CREATE VIEW \"v_actividad_usuarios\" AS SELECT u.id AS usuario_id,
    (((u.nombre)::text || ' '::text) || (u.apellido)::text) AS usuario,
    u.email,
    r.nombre AS rol,
    count(b.id) AS total_acciones,
    max(b.fecha) AS ultima_actividad,
    count(
        CASE
            WHEN ((b.accion)::text = 'LOGIN'::text) THEN 1
            ELSE NULL::integer
        END) AS total_logins
   FROM ((usuario u
     LEFT JOIN rol r ON ((u.rol_id = r.id)))
     LEFT JOIN bitacora b ON ((u.id = b.usuario_id)))
  GROUP BY u.id, u.nombre, u.apellido, u.email, r.nombre
  ORDER BY (max(b.fecha)) DESC NULLS LAST;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS \"v_actividad_usuarios\"");
    }
};
