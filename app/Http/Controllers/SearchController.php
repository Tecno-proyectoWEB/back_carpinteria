<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Material;
use App\Models\Pedido;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Búsqueda global en el sitio
     */
    public function buscar(Request $request)
    {
        $termino = $request->get('q', '');
        
        if (empty($termino)) {
            return response()->json([
                'productos' => [],
                'servicios' => [],
                'materiales' => [],
                'pedidos' => [],
            ]);
        }

        $resultados = [
            'productos' => [],
            'servicios' => [],
            'materiales' => [],
            'pedidos' => [],
        ];

        // Buscar productos
        $resultados['productos'] = Producto::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
            ->with('categoria')
            ->limit(10)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio_unitario,
                    'stock' => $producto->stock,
                    'tipo' => 'producto',
                    'ruta' => route('productos.show', $producto->id),
                ];
            });

        // Buscar servicios
        $resultados['servicios'] = Servicio::where('nombre', 'ILIKE', "%{$termino}%")
            ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
            ->where('activo', true)
            ->with('categoria')
            ->limit(10)
            ->get()
            ->map(function ($servicio) {
                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'precio' => $servicio->precio_base,
                    'tipo' => 'servicio',
                    'ruta' => route('servicios.show', $servicio->id),
                ];
            });

        // Buscar materiales (solo para roles autorizados)
        if (auth()->check() && (auth()->user()->tienePermiso('materiales.ver') || 
            auth()->user()->rol->nombre === 'PROVEEDOR')) {
            $resultados['materiales'] = Material::where('nombre', 'ILIKE', "%{$termino}%")
                ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                ->where('activo', true)
                ->with(['categoria', 'sector'])
                ->limit(10)
                ->get()
                ->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'nombre' => $material->nombre,
                        'descripcion' => $material->descripcion,
                        'precio' => $material->precio,
                        'stock' => $material->stock_actual,
                        'tipo' => 'material',
                        'ruta' => route('materiales.show', $material->id),
                    ];
                });
        }

        // Buscar pedidos (solo para roles autorizados)
        if (auth()->check() && (auth()->user()->tienePermiso('pedidos.ver'))) {
            $resultados['pedidos'] = Pedido::where('id', 'LIKE', "%{$termino}%")
                ->orWhere('descripcion', 'ILIKE', "%{$termino}%")
                ->with(['usuario', 'metodoPago'])
                ->limit(10)
                ->get()
                ->map(function ($pedido) {
                    return [
                        'id' => $pedido->id,
                        'descripcion' => $pedido->descripcion,
                        'total' => $pedido->importe_total_desc,
                        'fecha' => $pedido->fecha,
                        'cliente' => $pedido->usuario->nombre . ' ' . $pedido->usuario->apellido,
                        'tipo' => 'pedido',
                        'ruta' => route('pedidos.show', $pedido->id),
                    ];
                });
        }

        // Si es petición AJAX/Inertia, retornar JSON
        if ($request->expectsJson() || $request->header('X-Inertia')) {
            return response()->json($resultados);
        }

        // Si es petición normal, retornar vista Inertia
        return Inertia::render('Search/Results', [
            'termino' => $termino,
            'resultados' => $resultados,
        ]);
    }
}

