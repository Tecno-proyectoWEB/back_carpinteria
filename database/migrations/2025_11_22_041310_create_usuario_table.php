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
        Schema::create('usuario', function (Blueprint $table) {
            $table->comment('Tabla de usuarios del sistema con autenticación');
            $table->bigIncrements('id');
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('email')->index('idx_usuario_email');
            $table->string('telefono')->nullable();
            $table->string('password')->nullable();
            $table->boolean('estado')->nullable()->default(true);
            $table->boolean('disponibilidad')->nullable()->default(true);
            $table->boolean('cuenta_no_expirada')->nullable()->default(true);
            $table->boolean('cuenta_no_bloqueada')->nullable()->default(true);
            $table->boolean('credenciales_no_expiradas')->nullable()->default(true);
            $table->unsignedBigInteger('rol_id')->index('idx_usuario_rol');

            $table->unique(['email'], 'usuario_email_key');

            $table->foreign('rol_id')->references('id')->on('rol')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
