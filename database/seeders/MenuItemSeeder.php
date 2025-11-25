<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\Rol;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener roles
        $propietario = Rol::where('nombre', 'PROPIETARIO')->first();
        $carpintero = Rol::where('nombre', 'CARPINTERO')->first();
        $secretaria = Rol::where('nombre', 'SECRETARIA')->first();
        $cliente = Rol::where('nombre', 'CLIENTE')->first();
        $proveedor = Rol::where('nombre', 'PROVEEDOR')->first();

        // Limpiar menú existente
        MenuItem::truncate();
        \DB::table('menu_item_rol')->truncate();

        // Dashboard - Todos los roles
        $dashboard = MenuItem::create([
            'nombre' => 'Dashboard',
            'ruta' => '/',
            'icono' => 'home',
            'orden' => 1,
            'activo' => true,
        ]);
        $dashboard->roles()->attach([$propietario->id, $carpintero->id, $secretaria->id, $cliente->id, $proveedor->id]);

        // Productos - Propietario, Carpintero, Cliente
        $productos = MenuItem::create([
            'nombre' => 'Productos',
            'ruta' => '/productos',
            'icono' => 'box',
            'orden' => 2,
            'activo' => true,
        ]);
        $productos->roles()->attach([$propietario->id, $carpintero->id, $cliente->id]);

        // Servicios - Propietario, Carpintero, Cliente
        $servicios = MenuItem::create([
            'nombre' => 'Servicios',
            'ruta' => '/servicios',
            'icono' => 'tools',
            'orden' => 3,
            'activo' => true,
        ]);
        $servicios->roles()->attach([$propietario->id, $carpintero->id, $cliente->id]);

        // Materiales - Propietario, Carpintero, Proveedor
        $materiales = MenuItem::create([
            'nombre' => 'Materiales',
            'ruta' => '/materiales',
            'icono' => 'package',
            'orden' => 4,
            'activo' => true,
        ]);
        $materiales->roles()->attach([$propietario->id, $carpintero->id, $proveedor->id]);

        // Pedidos - Propietario, Secretaria, Cliente
        $pedidos = MenuItem::create([
            'nombre' => 'Pedidos',
            'ruta' => '/pedidos',
            'icono' => 'shopping-cart',
            'orden' => 5,
            'activo' => true,
        ]);
        $pedidos->roles()->attach([$propietario->id, $secretaria->id, $cliente->id]);

        // Compras - Propietario, Proveedor
        $compras = MenuItem::create([
            'nombre' => 'Compras',
            'ruta' => '/compras',
            'icono' => 'shopping-bag',
            'orden' => 6,
            'activo' => true,
        ]);
        $compras->roles()->attach([$propietario->id, $proveedor->id]);

        // Usuarios - Propietario, Secretaria
        $usuarios = MenuItem::create([
            'nombre' => 'Usuarios',
            'ruta' => '/usuarios',
            'icono' => 'users',
            'orden' => 7,
            'activo' => true,
        ]);
        $usuarios->roles()->attach([$propietario->id, $secretaria->id]);

        // Reportes - Propietario, Secretaria
        $reportes = MenuItem::create([
            'nombre' => 'Reportes',
            'ruta' => '/reportes',
            'icono' => 'chart-bar',
            'orden' => 8,
            'activo' => true,
        ]);
        $reportes->roles()->attach([$propietario->id, $secretaria->id]);

        // Inventario - Propietario, Carpintero
        $inventario = MenuItem::create([
            'nombre' => 'Inventario',
            'ruta' => '/inventario',
            'icono' => 'warehouse',
            'orden' => 9,
            'activo' => true,
        ]);
        $inventario->roles()->attach([$propietario->id, $carpintero->id]);
    }
}

