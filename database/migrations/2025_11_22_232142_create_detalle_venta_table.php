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
        Schema::create('detalle_venta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('producto_id')->nullable()->index('idx_detalle_venta_producto');
            $table->unsignedBigInteger('servicio_id')->nullable()->index('idx_detalle_venta_servicio');
            $table->unsignedBigInteger('venta_id')->nullable()->index('idx_detalle_venta_venta');
            $table->integer('cantidad')->default(1);
            $table->boolean('estado')->default(true);
            $table->decimal('importe_total', 10, 2)->default(0);
            $table->decimal('importe_total_desc', 10, 2)->default(0);
            $table->decimal('precio_unitario', 10, 2)->default(0);

            $table->foreign('producto_id')->references('id')->on('producto')->onDelete('set null');
            $table->foreign('servicio_id')->references('id')->on('servicio')->onDelete('set null');
            $table->foreign('venta_id')->references('id')->on('venta')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_venta');
    }
};

