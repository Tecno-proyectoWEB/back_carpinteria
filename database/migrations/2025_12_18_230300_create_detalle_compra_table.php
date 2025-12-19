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
        Schema::create('detalle_compra', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('cantidad');
            $table->double('precio_unitario');
            $table->double('subtotal');
            $table->double('descuento')->default(0);
            $table->double('importe_total');
            $table->unsignedBigInteger('compra_id')->index('idx_detalle_compra_compra');
            $table->unsignedBigInteger('material_id')->nullable()->index('idx_detalle_compra_material');
            
            $table->foreign('compra_id')->references('id')->on('compra')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('material')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_compra');
    }
};
