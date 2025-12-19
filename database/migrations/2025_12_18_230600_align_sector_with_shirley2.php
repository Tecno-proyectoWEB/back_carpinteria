<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sector', function (Blueprint $table) {
            if (!Schema::hasColumn('sector', 'stock')) {
                $table->double('stock')->nullable()->after('nombre');
            }
            if (!Schema::hasColumn('sector', 'capacidad_maxima')) {
                $table->double('capacidad_maxima')->nullable()->after('stock');
            }
            if (!Schema::hasColumn('sector', 'tipo')) {
                $table->string('tipo')->nullable()->after('capacidad_maxima');
            }
            if (!Schema::hasColumn('sector', 'almacen_id')) {
                $table->unsignedBigInteger('almacen_id')->nullable()->index('idx_sector_almacen')->after('descripcion');
                $table->foreign('almacen_id')->references('id')->on('almacen')->onDelete('set null');
            }
            if (!Schema::hasColumn('sector', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sector', function (Blueprint $table) {
            if (Schema::hasColumn('sector', 'almacen_id')) {
                $table->dropForeign(['almacen_id']);
                $table->dropIndex('idx_sector_almacen');
                $table->dropColumn('almacen_id');
            }
            if (Schema::hasColumn('sector', 'tipo')) {
                $table->dropColumn('tipo');
            }
            if (Schema::hasColumn('sector', 'capacidad_maxima')) {
                $table->dropColumn('capacidad_maxima');
            }
            if (Schema::hasColumn('sector', 'stock')) {
                $table->dropColumn('stock');
            }
            // No removemos 'activo' si ya existía previamente
        });
    }
};
