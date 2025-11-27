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
            $table->comment('Ventas de clientes');
            $table->bigIncrements('id');
            $table->timestamp('fecha')->nullable()->index('idx_venta_fecha');
            $table->string('descripcion')->nullable();
            $table->double('importe_total')->nullable();
            $table->double('importe_total_desc')->nullable();
            $table->boolean('estado')->nullable();
            $table->unsignedBigInteger('metodo_pago_id')->index('idx_venta_metodo_pago');
            $table->unsignedBigInteger('usuario_id')->index('idx_venta_usuario');

            $table->foreign('metodo_pago_id')->references('id')->on('metodo_pago')->onDelete('restrict');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('cascade');
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
