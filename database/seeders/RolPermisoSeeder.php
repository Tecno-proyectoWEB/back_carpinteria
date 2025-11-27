<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;

class RolPermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propietario = Rol::where('nombre', 'PROPIETARIO')->first();
        $carpintero = Rol::where('nombre', 'CARPINTERO')->first();
        $secretaria = Rol::where('nombre', 'SECRETARIA')->first();
        $cliente = Rol::where('nombre', 'CLIENTE')->first();
        $proveedor = Rol::where('nombre', 'PROVEEDOR')->first();

        $todosPermisos = Permiso::all();

        // Propietario tiene TODOS los permisos (acceso completo)
        if ($propietario && $todosPermisos->isNotEmpty()) {
            $propietario->permisos()->sync($todosPermisos->pluck('id'));
        }

        // Carpintero: productos, servicios, materiales, inventario
        if ($carpintero) {
            $permisosCarpintero = Permiso::whereIn('nombre', [
                'productos.ver', 'productos.crear', 'productos.editar',
                'servicios.ver', 'servicios.crear', 'servicios.editar',
                'materiales.ver', 'materiales.crear', 'materiales.editar',
                'inventario.ver', 'inventario.ingreso', 'inventario.salida',
            ])->pluck('id');
            $carpintero->permisos()->sync($permisosCarpintero);
        }

        // Secretaria: usuarios, pedidos, pagos, reportes, proveedores
        if ($secretaria) {
            $permisosSecretaria = Permiso::whereIn('nombre', [
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar',
                'pedidos.ver', 'pedidos.crear', 'pedidos.editar',
                'pagos.ver', 'pagos.crear', 'pagos.registrar',
                'proveedores.ver', 'proveedores.crear', 'proveedores.editar',
                'reportes.ver', 'reportes.exportar',
            ])->pluck('id');
            $secretaria->permisos()->sync($permisosSecretaria);
        }

        // Cliente: solo ver productos y servicios, crear pedidos
        if ($cliente) {
            $permisosCliente = Permiso::whereIn('nombre', [
                'productos.ver',
                'servicios.ver',
                'pedidos.ver',
                'pedidos.crear',
            ])->pluck('id');
            $cliente->permisos()->sync($permisosCliente);
        }

        // Proveedor: ver compras y materiales
        if ($proveedor) {
            $permisosProveedor = Permiso::whereIn('nombre', [
                'compras.ver',
                'materiales.ver',
            ])->pluck('id');
            $proveedor->permisos()->sync($permisosProveedor);
        }
    }
}
