<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Renombrar tabla pedido a venta
        DB::statement('ALTER TABLE pedido RENAME TO venta');
        
        // Renombrar tabla detalle_pedido a detalle_venta
        DB::statement('ALTER TABLE detalle_pedido RENAME TO detalle_venta');
        
        // Actualizar foreign keys en detalle_venta (PostgreSQL no soporta renameColumn directamente)
        DB::statement('ALTER TABLE detalle_venta DROP CONSTRAINT IF EXISTS detalle_pedido_pedido_id_foreign');
        DB::statement('ALTER TABLE detalle_venta RENAME COLUMN pedido_id TO venta_id');
        DB::statement('ALTER TABLE detalle_venta ADD CONSTRAINT detalle_venta_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE');
        
        // Actualizar foreign keys en pago
        DB::statement('ALTER TABLE pago DROP CONSTRAINT IF EXISTS pago_pedido_id_foreign');
        DB::statement('ALTER TABLE pago RENAME COLUMN pedido_id TO venta_id');
        DB::statement('ALTER TABLE pago ADD CONSTRAINT pago_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE CASCADE');
        
        // Actualizar foreign keys en movimiento_inventario
        if (Schema::hasColumn('movimiento_inventario', 'pedido_id')) {
            DB::statement('ALTER TABLE movimiento_inventario DROP CONSTRAINT IF EXISTS movimiento_inventario_pedido_id_foreign');
            DB::statement('ALTER TABLE movimiento_inventario RENAME COLUMN pedido_id TO venta_id');
            DB::statement('ALTER TABLE movimiento_inventario ADD CONSTRAINT movimiento_inventario_venta_id_foreign FOREIGN KEY (venta_id) REFERENCES venta(id) ON DELETE SET NULL');
        }
        
        // Actualizar índices
        DB::statement('ALTER INDEX IF EXISTS idx_detalle_pedido_pedido RENAME TO idx_detalle_venta_venta');
        DB::statement('ALTER INDEX IF EXISTS idx_pago_pedido RENAME TO idx_pago_venta');
        if (Schema::hasColumn('movimiento_inventario', 'venta_id')) {
            DB::statement('ALTER INDEX IF EXISTS idx_movimiento_pedido RENAME TO idx_movimiento_venta');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir foreign keys en movimiento_inventario
        if (Schema::hasColumn('movimiento_inventario', 'venta_id')) {
            DB::statement('ALTER TABLE movimiento_inventario DROP CONSTRAINT IF EXISTS movimiento_inventario_venta_id_foreign');
            DB::statement('ALTER TABLE movimiento_inventario RENAME COLUMN venta_id TO pedido_id');
            DB::statement('ALTER TABLE movimiento_inventario ADD CONSTRAINT movimiento_inventario_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE SET NULL');
            DB::statement('ALTER INDEX IF EXISTS idx_movimiento_venta RENAME TO idx_movimiento_pedido');
        }
        
        // Revertir foreign keys en pago
        DB::statement('ALTER TABLE pago DROP CONSTRAINT IF EXISTS pago_venta_id_foreign');
        DB::statement('ALTER TABLE pago RENAME COLUMN venta_id TO pedido_id');
        DB::statement('ALTER TABLE pago ADD CONSTRAINT pago_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE');
        DB::statement('ALTER INDEX IF EXISTS idx_pago_venta RENAME TO idx_pago_pedido');
        
        // Revertir foreign keys en detalle_venta
        DB::statement('ALTER TABLE detalle_venta DROP CONSTRAINT IF EXISTS detalle_venta_venta_id_foreign');
        DB::statement('ALTER TABLE detalle_venta RENAME COLUMN venta_id TO pedido_id');
        DB::statement('ALTER TABLE detalle_venta ADD CONSTRAINT detalle_pedido_pedido_id_foreign FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE');
        DB::statement('ALTER INDEX IF EXISTS idx_detalle_venta_venta RENAME TO idx_detalle_pedido_pedido');
        
        // Renombrar tablas de vuelta
        DB::statement('ALTER TABLE detalle_venta RENAME TO detalle_pedido');
        DB::statement('ALTER TABLE venta RENAME TO pedido');
    }
};

