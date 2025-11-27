<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Material;
use App\Models\Venta;
use App\Models\Usuario;
use Inertia\Inertia;

class BusquedaController extends Controller
{
    public function buscar(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (empty($query)) {
            return Inertia::render('Busqueda/Resultados', [
                'query' => $query,
                'resultados' => [
                    'productos' => [],
                    'servicios' => [],
                    'materiales' => [],
                    'ventas' => [],
                    'usuarios' => [],
                ],
            ]);
        }

        $searchTerm = "%{$query}%";

        // Productos: buscar en nombre, descripción, tiempo, precio
        $productos = Producto::where(function($q) use ($searchTerm, $query) {
            $q->where('nombre', 'ilike', $searchTerm)
              ->orWhere('descripcion', 'ilike', $searchTerm)
              ->orWhere('tiempo', 'ilike', $searchTerm);
            if (is_numeric($query)) {
                $q->orWhere('precio_unitario', '=', (float)$query)
                  ->orWhere('stock', '=', (int)$query);
            }
        })->with('categoria')->limit(10)->get();

        // Servicios: buscar en nombre, descripción, tiempo_estimado, precio
        $servicios = Servicio::where(function($q) use ($searchTerm, $query) {
            $q->where('nombre', 'ilike', $searchTerm)
              ->orWhere('descripcion', 'ilike', $searchTerm);
            if (is_numeric($query)) {
                $q->orWhere('precio_base', '=', (float)$query)
                  ->orWhere('tiempo_estimado', '=', (int)$query);
            }
        })->with('categoria')->limit(10)->get();

        // Materiales: buscar en nombre, descripcion, unidad_medida, categoria_text
        $materiales = Material::where(function($q) use ($searchTerm, $query) {
            $q->where('nombre', 'ilike', $searchTerm)
              ->orWhere('descripcion', 'ilike', $searchTerm)
              ->orWhere('unidad_medida', 'ilike', $searchTerm)
              ->orWhere('categoria_text', 'ilike', $searchTerm);
            if (is_numeric($query)) {
                $q->orWhere('precio', '=', (float)$query)
                  ->orWhere('stock_actual', '=', (int)$query);
            }
        })->with('categoria')->limit(10)->get();

        // Ventas: buscar por ID (si es numérico) o descripción
        $ventasQuery = Venta::query();
        if (is_numeric($query)) {
            $ventasQuery->where(function($q) use ($query, $searchTerm) {
                $q->where('id', '=', (int)$query)
                  ->orWhere('descripcion', 'ilike', $searchTerm);
            });
        } else {
            $ventasQuery->where('descripcion', 'ilike', $searchTerm);
        }
        $ventas = $ventasQuery->with(['usuario', 'metodoPago'])->limit(10)->get();

        // Usuarios: buscar en nombre, apellido, email, teléfono
        $usuarios = Usuario::where(function($q) use ($searchTerm, $query) {
            $q->where('nombre', 'ilike', $searchTerm)
              ->orWhere('apellido', 'ilike', $searchTerm)
              ->orWhere('email', 'ilike', $searchTerm)
              ->orWhere('telefono', 'ilike', $searchTerm);
        })->with('rol')->limit(10)->get();

        return Inertia::render('Busqueda/Resultados', [
            'query' => $query,
            'resultados' => [
                'productos' => $productos,
                'servicios' => $servicios,
                'materiales' => $materiales,
                'ventas' => $ventas,
                'usuarios' => $usuarios,
            ],
        ]);
    }
}
