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
            $table->boolean('activo')->default(true)->after('descripcion');
            $table->boolean('es_electronico')->default(false)->after('activo');
            $table->string('tipo_electronico', 50)->nullable()->after('es_electronico')->comment('QR, TARJETA, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('metodo_pago', function (Blueprint $table) {
            $table->dropColumn(['activo', 'es_electronico', 'tipo_electronico']);
        });
    }
};

