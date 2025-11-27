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

        // Propietario tiene todos los permisos
        if ($propietario) {
            $propietario->permisos()->sync($todosPermisos->pluck('id'));
        }

        // Carpintero: productos, servicios, materiales, inventario
        if ($carpintero) {
            $permisosCarpintero = Permiso::whereIn('nombre', [
                'productos.ver', 'productos.crear', 'productos.editar',
                'servicios.ver', 'servicios.crear', 'servicios.editar',
                'materiales.ver', 'materiales.crear', 'materiales.editar',
                'insumos.ver', 'insumos.crear', 'insumos.editar',
                'inventario.ver', 'inventario.ingreso', 'inventario.salida',
            ])->pluck('id');
            $carpintero->permisos()->sync($permisosCarpintero);
        }

        // Secretaria: usuarios, ventas, pagos, reportes, roles
        if ($secretaria) {
            $permisosSecretaria = Permiso::whereIn('nombre', [
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar',
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.aprobar',
                'pagos.ver', 'pagos.crear', 'pagos.registrar',
                'reportes.ver', 'reportes.exportar',
                'roles.ver', 'roles.crear', 'roles.editar',
            ])->pluck('id');
            $secretaria->permisos()->sync($permisosSecretaria);
        }

        // Cliente: solo ver productos y servicios, crear ventas
        if ($cliente) {
            $permisosCliente = Permiso::whereIn('nombre', [
                'productos.ver',
                'servicios.ver',
                'ventas.crear',
            ])->pluck('id');
            $cliente->permisos()->sync($permisosCliente);
        }

        // Proveedor: ver y gestionar compras
        if ($proveedor) {
            $permisosProveedor = Permiso::whereIn('nombre', [
                'compras.ver',
                'materiales.ver',
                'insumos.ver',
            ])->pluck('id');
            $proveedor->permisos()->sync($permisosProveedor);
        }
    }
}
