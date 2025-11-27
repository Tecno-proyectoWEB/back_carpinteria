<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\MenuItem;
use Inertia\Inertia;

class MenuController extends Controller
{
    /**
     * Obtiene el menú según el rol del usuario
     */
    public function getMenuForUser($user)
    {
        if (!$user) {
            \Log::warning('getMenuForUser llamado sin usuario');
            return [];
        }

        // Asegurar que el rol esté cargado
        if (!$user->relationLoaded('rol')) {
            $user->load('rol');
        }

        if (!$user->rol) {
            \Log::warning('Usuario sin rol asignado', ['user_id' => $user->id]);
            return [];
        }

        // Intentar obtener menú desde base de datos
        try {
            $menuItems = MenuItem::getMenuForRol($user->rol->id);

            // Si hay items en BD, retornarlos
            if ($menuItems && $menuItems->count() > 0) {
                return $menuItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'ruta' => $item->ruta,
                        'icono' => $item->icono,
                        'orden' => $item->orden,
                        'children' => $item->children ? $item->children->map(function ($child) {
                            return [
                                'id' => $child->id,
                                'nombre' => $child->nombre,
                                'ruta' => $child->ruta,
                                'icono' => $child->icono,
                            ];
                        })->toArray() : [],
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            \Log::error('Error al obtener menú de BD: ' . $e->getMessage());
        }

        // Fallback: menú hardcodeado si no hay items en BD
        return $this->getMenuFallback($user->rol->nombre);
    }

    /**
     * Menú fallback (hardcodeado) mientras se configura en BD
     */
    private function getMenuFallback($rolNombre)
    {
        $menu = [
            [
                'id' => 1,
                'nombre' => 'Dashboard',
                'ruta' => route('dashboard'),
                'icono' => 'home',
                'orden' => 1,
                'children' => [],
            ],
        ];

        switch ($rolNombre) {
            case 'PROPIETARIO':
                $menu = array_merge($menu, [
                    ['id' => 2, 'nombre' => 'Productos', 'ruta' => route('productos.index'), 'icono' => 'box', 'orden' => 2, 'children' => []],
                    ['id' => 3, 'nombre' => 'Servicios', 'ruta' => route('servicios.index'), 'icono' => 'tools', 'orden' => 3, 'children' => []],
                    ['id' => 4, 'nombre' => 'Materiales', 'ruta' => route('materiales.index'), 'icono' => 'package', 'orden' => 4, 'children' => []],
                    ['id' => 5, 'nombre' => 'Pedidos', 'ruta' => route('pedidos.index'), 'icono' => 'shopping-cart', 'orden' => 5, 'children' => []],
                    ['id' => 6, 'nombre' => 'Compras', 'ruta' => route('compras.index'), 'icono' => 'shopping-bag', 'orden' => 6, 'children' => []],
                    ['id' => 7, 'nombre' => 'Usuarios', 'ruta' => route('usuarios.index'), 'icono' => 'users', 'orden' => 7, 'children' => []],
                    ['id' => 10, 'nombre' => 'Roles', 'ruta' => route('roles.index'), 'icono' => 'shield', 'orden' => 10, 'children' => []],
                    ['id' => 8, 'nombre' => 'Reportes', 'ruta' => route('reportes.index'), 'icono' => 'chart-bar', 'orden' => 8, 'children' => []],
                    ['id' => 9, 'nombre' => 'Inventario', 'ruta' => route('inventario.index'), 'icono' => 'warehouse', 'orden' => 9, 'children' => []],
                ]);
                break;

            case 'CARPINTERO':
                $menu = array_merge($menu, [
                    ['id' => 2, 'nombre' => 'Productos', 'ruta' => route('productos.index'), 'icono' => 'box', 'orden' => 2, 'children' => []],
                    ['id' => 3, 'nombre' => 'Servicios', 'ruta' => route('servicios.index'), 'icono' => 'tools', 'orden' => 3, 'children' => []],
                    ['id' => 4, 'nombre' => 'Materiales', 'ruta' => route('materiales.index'), 'icono' => 'package', 'orden' => 4, 'children' => []],
                    ['id' => 9, 'nombre' => 'Inventario', 'ruta' => route('inventario.index'), 'icono' => 'warehouse', 'orden' => 9, 'children' => []],
                ]);
                break;

            case 'SECRETARIA':
                $menu = array_merge($menu, [
                    ['id' => 5, 'nombre' => 'Pedidos', 'ruta' => route('pedidos.index'), 'icono' => 'shopping-cart', 'orden' => 5, 'children' => []],
                    ['id' => 7, 'nombre' => 'Usuarios', 'ruta' => route('usuarios.index'), 'icono' => 'users', 'orden' => 7, 'children' => []],
                    ['id' => 8, 'nombre' => 'Reportes', 'ruta' => route('reportes.index'), 'icono' => 'chart-bar', 'orden' => 8, 'children' => []],
                ]);
                break;

            case 'CLIENTE':
                $menu = array_merge($menu, [
                    ['id' => 2, 'nombre' => 'Productos', 'ruta' => route('productos.index'), 'icono' => 'box', 'orden' => 2, 'children' => []],
                    ['id' => 3, 'nombre' => 'Servicios', 'ruta' => route('servicios.index'), 'icono' => 'tools', 'orden' => 3, 'children' => []],
                    ['id' => 10, 'nombre' => 'Mis Pedidos', 'ruta' => route('pedidos.mis-pedidos'), 'icono' => 'list', 'orden' => 10, 'children' => []],
                ]);
                break;

            case 'PROVEEDOR':
                $menu = array_merge($menu, [
                    ['id' => 4, 'nombre' => 'Materiales', 'ruta' => route('materiales.index'), 'icono' => 'package', 'orden' => 4, 'children' => []],
                    ['id' => 11, 'nombre' => 'Mis Compras', 'ruta' => route('compras.mis-compras'), 'icono' => 'shopping-bag', 'orden' => 11, 'children' => []],
                ]);
                break;
        }

        return $menu;
    }
}
