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
        Schema::create('visita', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ruta', 255)->index('idx_visita_ruta');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable()->index('idx_visita_usuario');
            $table->timestamp('created_at')->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visita');
    }
};

