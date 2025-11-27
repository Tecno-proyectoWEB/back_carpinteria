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
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->comment('Registro de movimientos de inventario (ingresos y salidas)');
            $table->bigIncrements('id');
            $table->string('tipo', 20)->comment('INGRESO o SALIDA');
            $table->integer('cantidad');
            $table->string('motivo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha')->useCurrent();
            $table->unsignedBigInteger('material_id')->nullable()->index('idx_movimiento_material');
            $table->unsignedBigInteger('producto_id')->nullable()->index('idx_movimiento_producto');
            $table->unsignedBigInteger('usuario_id')->index('idx_movimiento_usuario');
            $table->unsignedBigInteger('venta_id')->nullable()->index('idx_movimiento_venta');

            $table->foreign('material_id')->references('id')->on('material')->onDelete('set null');
            $table->foreign('producto_id')->references('id')->on('producto')->onDelete('set null');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('restrict');
            $table->foreign('venta_id')->references('id')->on('venta')->onDelete('set null');

            $table->index(['tipo', 'fecha'], 'idx_movimiento_tipo_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
