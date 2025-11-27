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
            
            // Roles
            ['nombre' => 'roles.ver'],
            ['nombre' => 'roles.crear'],
            ['nombre' => 'roles.editar'],
            ['nombre' => 'roles.eliminar'],
            
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
            
            // Materiales (antes insumos)
            ['nombre' => 'materiales.ver'],
            ['nombre' => 'materiales.crear'],
            ['nombre' => 'materiales.editar'],
            ['nombre' => 'materiales.eliminar'],
            
            // Proveedores
            ['nombre' => 'proveedores.ver'],
            ['nombre' => 'proveedores.crear'],
            ['nombre' => 'proveedores.editar'],
            ['nombre' => 'proveedores.eliminar'],
            
            // Pedidos (Ventas)
            ['nombre' => 'pedidos.ver'],
            ['nombre' => 'pedidos.crear'],
            ['nombre' => 'pedidos.editar'],
            ['nombre' => 'pedidos.anular'],
            
            // Compras
            ['nombre' => 'compras.ver'],
            ['nombre' => 'compras.crear'],
            ['nombre' => 'compras.editar'],
            ['nombre' => 'compras.eliminar'],
            ['nombre' => 'compras.confirmar'],
            
            // Inventario
            ['nombre' => 'inventario.ver'],
            ['nombre' => 'inventario.ingreso'],
            ['nombre' => 'inventario.salida'],
            
            // Pagos
            ['nombre' => 'pagos.ver'],
            ['nombre' => 'pagos.crear'],
            ['nombre' => 'pagos.registrar'],
            
            // Reportes
            ['nombre' => 'reportes.ver'],
            ['nombre' => 'reportes.exportar'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::firstOrCreate(['nombre' => $permiso['nombre']], $permiso);
        }
    }
}
