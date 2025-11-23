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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->comment('Registro de auditoría de todas las acciones del sistema');
            $table->bigIncrements('id');
            $table->string('accion', 50)->index('idx_bitacora_accion');
            $table->string('modulo', 50)->index('idx_bitacora_modulo');
            $table->string('tabla_afectada', 100)->nullable()->index('idx_bitacora_tabla');
            $table->bigInteger('registro_id')->nullable();
            $table->jsonb('datos_anteriores')->nullable();
            $table->jsonb('datos_nuevos')->nullable();
            $table->unsignedBigInteger('usuario_id')->index('idx_bitacora_usuario');
            $table->string('direccion_ip', 45)->nullable();
            $table->string('navegador')->nullable();
            $table->timestamp('fecha')->useCurrent()->index('idx_bitacora_fecha');
            $table->unsignedInteger('tipo_accion_id')->nullable()->index('idx_bitacora_tipo_accion');

            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('cascade');
            $table->foreign('tipo_accion_id')->references('id')->on('tipo_accion')->onDelete('set null');

            $table->index(['fecha', 'modulo'], 'idx_bitacora_fecha_modulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
