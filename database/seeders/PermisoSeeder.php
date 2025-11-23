<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permiso;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            // Usuarios
            ['nombre' => 'usuarios.ver'],
            ['nombre' => 'usuarios.crear'],
            ['nombre' => 'usuarios.editar'],
            ['nombre' => 'usuarios.eliminar'],
            
            // Productos
            ['nombre' => 'productos.ver'],
            ['nombre' => 'productos.crear'],
            ['nombre' => 'productos.editar'],
            ['nombre' => 'productos.eliminar'],
            
            // Servicios
            ['nombre' => 'servicios.ver'],
            ['nombre' => 'servicios.crear'],
            ['nombre' => 'servicios.editar'],
            ['nombre' => 'servicios.eliminar'],
            
            // Insumos/Materiales
            ['nombre' => 'insumos.ver'],
            ['nombre' => 'insumos.crear'],
            ['nombre' => 'insumos.editar'],
            ['nombre' => 'insumos.eliminar'],
            
            // Inventario
            ['nombre' => 'inventario.ver'],
            ['nombre' => 'inventario.ingreso'],
            ['nombre' => 'inventario.salida'],
            
            // Ventas
            ['nombre' => 'ventas.ver'],
            ['nombre' => 'ventas.crear'],
            ['nombre' => 'ventas.editar'],
            ['nombre' => 'ventas.anular'],
            
            // Pagos
            ['nombre' => 'pagos.ver'],
            ['nombre' => 'pagos.crear'],
            ['nombre' => 'pagos.registrar'],
            
            // Compras
            ['nombre' => 'compras.ver'],
            ['nombre' => 'compras.crear'],
            ['nombre' => 'compras.editar'],
            ['nombre' => 'compras.eliminar'],
            
            // Reportes
            ['nombre' => 'reportes.ver'],
            ['nombre' => 'reportes.exportar'],
            
            // Configuración
            ['nombre' => 'configuracion.ver'],
            ['nombre' => 'configuracion.editar'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['nombre' => $permiso['nombre']], $permiso);
        }
    }
}
