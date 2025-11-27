<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metodo_pago', function (Blueprint $table) {
            $table->boolean('es_electronico')->default(false)->after('descripcion');
            $table->string('tipo_electronico')->nullable()->after('es_electronico'); // TARJETA, TRANSFERENCIA, QR, etc.
            $table->string('numero_cuenta')->nullable()->after('tipo_electronico');
            $table->string('entidad_financiera')->nullable()->after('numero_cuenta');
            $table->boolean('activo')->default(true)->after('entidad_financiera');
        });
    }

    public function down(): void
    {
        Schema::table('metodo_pago', function (Blueprint $table) {
            $table->dropColumn(['es_electronico', 'tipo_electronico', 'numero_cuenta', 'entidad_financiera', 'activo']);
        });
    }
};
