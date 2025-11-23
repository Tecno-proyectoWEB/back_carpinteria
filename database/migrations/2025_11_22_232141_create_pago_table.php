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
        Schema::create('pago', function (Blueprint $table) {
            $table->comment('Registro de pagos de pedidos');
            $table->bigIncrements('id');
            $table->decimal('monto', 10, 2);
            $table->timestamp('fecha_pago')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('estado', 20)->default('PENDIENTE')->comment('PENDIENTE, PAGADO, VENCIDO, CANCELADO');
            $table->string('tipo', 20)->comment('CONTADO, CREDITO, CUOTA');
            $table->integer('numero_cuota')->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('pedido_id')->index('idx_pago_pedido');
            $table->unsignedBigInteger('metodo_pago_id')->index('idx_pago_metodo_pago');
            $table->unsignedBigInteger('usuario_id')->nullable()->index('idx_pago_usuario');

            $table->foreign('pedido_id')->references('id')->on('pedido')->onDelete('cascade');
            $table->foreign('metodo_pago_id')->references('id')->on('metodo_pago')->onDelete('restrict');
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');

            $table->index(['estado', 'fecha_vencimiento'], 'idx_pago_estado_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago');
    }
};
