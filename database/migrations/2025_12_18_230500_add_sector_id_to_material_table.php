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
        Schema::table('material', function (Blueprint $table) {
            $table->unsignedBigInteger('sector_id')->nullable()->index('idx_material_sector');
            $table->foreign('sector_id')->references('id')->on('sector')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropIndex('idx_material_sector');
            $table->dropColumn('sector_id');
        });
    }
};
