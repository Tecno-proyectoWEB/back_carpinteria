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
        Schema::create('sector', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre')->nullable();
            $table->double('stock')->nullable();
            $table->double('capacidad_maxima')->nullable();
            $table->string('tipo')->nullable();
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('almacen_id')->nullable()->index('idx_sector_almacen');

            $table->foreign('almacen_id')->references('id')->on('almacen')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sector');
    }
};
