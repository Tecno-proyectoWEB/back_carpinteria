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
        Schema::create('detalle_devolucion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cantidad')->nullable();
            $table->double('importe_total')->nullable();
            $table->string('motivo_detalle')->nullable();
            $table->unsignedBigInteger('devolucion_id')->index('idx_detalle_devolucion_devolucion');
            $table->unsignedBigInteger('detalle_pedido_id')->index('idx_detalle_devolucion_detalle_pedido');

            $table->foreign('devolucion_id')->references('id')->on('devolucion')->onDelete('cascade');
            $table->foreign('detalle_pedido_id')->references('id')->on('detalle_pedido')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_devolucion');
    }
};
