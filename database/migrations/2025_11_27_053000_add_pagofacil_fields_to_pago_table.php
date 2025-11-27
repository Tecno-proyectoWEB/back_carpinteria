<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->string('nro_pago')->unique()->nullable()->after('id');
            $table->string('nro_transaccion')->nullable()->after('nro_pago');
            $table->string('qr_image')->nullable()->after('nro_transaccion');
            $table->timestamp('qr_expires_at')->nullable()->after('qr_image');
            $table->timestamp('fecha_confirmacion')->nullable()->after('fecha_pago');
            $table->string('metodo_pago_facil')->nullable()->after('fecha_confirmacion'); // QR BNB, QR BCP, etc.
        });
    }

    public function down(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->dropColumn([
                'nro_pago',
                'nro_transaccion',
                'qr_image',
                'qr_expires_at',
                'fecha_confirmacion',
                'metodo_pago_facil'
            ]);
        });
    }
};

