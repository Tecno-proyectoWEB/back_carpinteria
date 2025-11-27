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
        Schema::create('venta', function (Blueprint $table) {
            $table->comment('Ventas de productos y servicios');
            $table->bigIncrements('id');
            $table->timestamp('fecha')->nullable()->index('idx_venta_fecha');
            $table->text('descripcion')->nullable();
            $table->decimal('importe_total', 10, 2)->default(0);
            $table->decimal('importe_total_desc', 10, 2)->default(0);
            $table->boolean('estado')->default(false)->comment('true = completado, false = pendiente');
            $table->unsignedBigInteger('metodo_pago_id')->index('idx_venta_metodo_pago');
            $table->unsignedBigInteger('usuario_id')->index('idx_venta_usuario');

            $table->foreign('metodo_pago_id')->references('id')->on('metodo_pago')->onDelete('restrict');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venta');
    }
};

