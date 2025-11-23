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
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('producto_id')->nullable()->index('idx_detalle_pedido_producto');
            $table->unsignedBigInteger('pedido_id')->nullable()->index('idx_detalle_pedido_pedido');
            $table->integer('cantidad');
            $table->boolean('estado')->nullable()->default(false);
            $table->double('importe_total')->nullable()->default(0);
            $table->double('importe_total_desc')->nullable()->default(0);
            $table->double('precio_unitario')->nullable();

            $table->foreign('producto_id')->references('id')->on('producto')->onDelete('set null');
            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
