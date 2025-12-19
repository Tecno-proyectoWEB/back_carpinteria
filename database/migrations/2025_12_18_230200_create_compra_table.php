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
            $table->string('estado', 50)->default('PENDIENTE'); // PENDIENTE, COMPLETADA, CANCELADA
            $table->timestamp('fecha')->useCurrent();
            $table->double('importe_total')->default(0);
            $table->double('importe_descuento')->default(0);
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('proveedor_id')->nullable()->index('idx_compra_proveedor');
            $table->unsignedBigInteger('usuario_id')->nullable()->index('idx_compra_usuario');
            
            $table->foreign('proveedor_id')->references('id')->on('proveedor')->onDelete('set null');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
            
            $table->timestamps();
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
