<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visita', function (Blueprint $table) {
            $table->id();
            $table->string('ruta');
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->onDelete('set null');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visita');
    }
};
