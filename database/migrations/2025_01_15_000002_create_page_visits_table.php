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
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('ruta'); // Ruta de la página (ej: /productos, /dashboard)
            $table->string('nombre_pagina'); // Nombre descriptivo de la página
            $table->integer('contador')->default(0);
            $table->timestamp('ultima_visita')->nullable();
            $table->timestamps();

            // Índice único para ruta
            $table->unique('ruta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};

