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
        Schema::create('devolucion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('fecha')->nullable();
            $table->string('motivo')->nullable();
            $table->string('descripcion')->nullable();
            $table->double('importe_total')->nullable();
            $table->boolean('estado')->nullable()->default(false);
            $table->unsignedBigInteger('usuario_id')->nullable()->index('idx_devolucion_usuario');
            $table->unsignedBigInteger('pedido_id')->nullable()->index('idx_devolucion_pedido');

            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devolucion');
    }
};
