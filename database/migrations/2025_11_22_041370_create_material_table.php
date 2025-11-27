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
        Schema::create('material', function (Blueprint $table) {
            $table->comment('Materiales/insumos utilizados en la producción');
            $table->bigIncrements('id');
            $table->string('nombre')->nullable();
            $table->string('descripcion')->nullable();
            $table->string('unidad_medida')->nullable();
            $table->double('precio')->nullable();
            $table->integer('stock_actual')->nullable();
            $table->integer('stock_minimo')->nullable();
            $table->integer('punto_reorden')->nullable();
            $table->string('categoria_text')->nullable();
            $table->boolean('activo')->nullable()->default(true);
            $table->string('imagen')->nullable();
            $table->unsignedBigInteger('categoria_id')->nullable()->index('idx_material_categoria');

            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material');
    }
};
