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
        Schema::create('compra', function (Blueprint $table) {
            $table->comment('Compras de materiales a proveedores');
            $table->bigIncrements('id');
            $table->string('estado')->nullable();
            $table->timestamp('fecha')->nullable();
            $table->double('importe_total')->nullable();
            $table->double('importe_descuento')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable()->index('idx_compra_proveedor');
            $table->unsignedBigInteger('usuario_id')->nullable()->index('idx_compra_usuario');

            $table->foreign('proveedor_id')->references('id')->on('proveedor')->onDelete('set null');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compra');
    }
};
