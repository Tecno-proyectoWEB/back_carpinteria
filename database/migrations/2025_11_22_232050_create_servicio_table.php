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
        Schema::create('servicio', function (Blueprint $table) {
            $table->comment('Servicios ofrecidos por la carpintería');
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio_base', 10, 2);
            $table->integer('tiempo_estimado')->nullable()->comment('Tiempo estimado en horas');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('categoria_id')->nullable()->index('idx_servicio_categoria');

            $table->foreign('categoria_id')->references('id')->on('categoria')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio');
    }
};
