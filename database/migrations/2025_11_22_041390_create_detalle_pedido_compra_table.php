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
        Schema::create('detalle_pedido_compra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cantidad');
            $table->string('estado')->nullable();
            $table->double('importe')->nullable();
            $table->double('importe_desc')->nullable();
            $table->double('precio')->nullable();
            $table->unsignedBigInteger('compra_id')->nullable()->index('idx_detalle_pedido_compra_compra');
            $table->unsignedBigInteger('material_id')->nullable()->index('idx_detalle_pedido_compra_material');

            $table->foreign('compra_id')->references('id')->on('compra')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('material')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_compra');
    }
};
