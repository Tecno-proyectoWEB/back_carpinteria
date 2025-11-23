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
        Schema::create('producto', function (Blueprint $table) {
            $table->comment('Productos finales fabricados');
            $table->bigIncrements('id');
            $table->string('nombre')->nullable();
            $table->string('descripcion')->nullable();
            $table->integer('stock')->nullable();
            $table->integer('stock_minimo')->nullable();
            $table->string('imagen')->nullable();
            $table->string('tiempo')->nullable();
            $table->double('precio_unitario')->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable()->index('idx_producto_categoria');
            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
