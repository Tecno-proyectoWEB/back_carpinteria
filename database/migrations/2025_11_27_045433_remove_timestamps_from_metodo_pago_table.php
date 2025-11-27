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
        Schema::table('metodo_pago', function (Blueprint $table) {
            // Eliminar timestamps para mantener coherencia con el modelo
            $table->dropTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metodo_pago', function (Blueprint $table) {
            $table->timestamps();
        });
    }
};
