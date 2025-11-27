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
        Schema::table('pago', function (Blueprint $table) {
            $table->string('nro_pago', 50)->nullable()->unique()->after('id')->comment('Número de pago único para PagoFácil');
            $table->string('nro_transaccion', 100)->nullable()->after('nro_pago')->comment('TransactionId de PagoFácil');
            $table->text('qr_image')->nullable()->after('nro_transaccion')->comment('URL o base64 del QR generado');
            $table->timestamp('qr_expires_at')->nullable()->after('qr_image')->comment('Fecha de expiración del QR');
            $table->timestamp('fecha_confirmacion')->nullable()->after('qr_expires_at')->comment('Fecha de confirmación del pago desde PagoFácil');
            $table->string('metodo_pago_facil', 50)->nullable()->after('fecha_confirmacion')->comment('Método de pago usado en PagoFácil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago', function (Blueprint $table) {
            $table->dropColumn([
                'nro_pago',
                'nro_transaccion',
                'qr_image',
                'qr_expires_at',
                'fecha_confirmacion',
                'metodo_pago_facil',
            ]);
        });
    }
};

