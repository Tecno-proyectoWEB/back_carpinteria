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
        Schema::create('tipo_accion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 50)->unique('tipo_accion_codigo_key');
            $table->string('descripcion')->nullable();
            $table->string('modulo', 50)->nullable();
        });

        // Insertar tipos de acción predeterminados
        DB::table('tipo_accion')->insert([
            ['codigo' => 'CREATE', 'descripcion' => 'Creación de registro', 'modulo' => 'GENERAL'],
            ['codigo' => 'UPDATE', 'descripcion' => 'Actualización de registro', 'modulo' => 'GENERAL'],
            ['codigo' => 'DELETE', 'descripcion' => 'Eliminación de registro', 'modulo' => 'GENERAL'],
            ['codigo' => 'LOGIN', 'descripcion' => 'Inicio de sesión', 'modulo' => 'SEGURIDAD'],
            ['codigo' => 'LOGOUT', 'descripcion' => 'Cierre de sesión', 'modulo' => 'SEGURIDAD'],
            ['codigo' => 'PAGO', 'descripcion' => 'Registro de pago', 'modulo' => 'PAGOS'],
            ['codigo' => 'VENTA', 'descripcion' => 'Registro de venta', 'modulo' => 'VENTAS'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_accion');
    }
};
