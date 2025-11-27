<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Material;
use App\Models\Venta;
use App\Models\Pago;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusquedaController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return Inertia::render('Busqueda/Resultados', [
                'query' => $query,
                'resultados' => [
                    'productos' => [],
                    'servicios' => [],
                    'materiales' => [],
                    'ventas' => [],
                    'pagos' => [],
                ],
            ]);
        }

        $resultados = [
            'productos' => Producto::where('nombre', 'ilike', "%{$query}%")
                ->orWhere('descripcion', 'ilike', "%{$query}%")
                ->limit(10)
                ->get(),
            'servicios' => Servicio::where('nombre', 'ilike', "%{$query}%")
                ->orWhere('descripcion', 'ilike', "%{$query}%")
                ->limit(10)
                ->get(),
            'materiales' => Material::where('nombre', 'ilike', "%{$query}%")
                ->orWhere('descripcion', 'ilike', "%{$query}%")
                ->limit(10)
                ->get(),
            'ventas' => Venta::with(['usuario', 'metodoPago'])
                ->whereHas('usuario', function($q) use ($query) {
                    $q->where('nombre', 'ilike', "%{$query}%")
                      ->orWhere('apellido', 'ilike', "%{$query}%");
                })
                ->orWhere('id', '=', $query)
                ->limit(10)
                ->get(),
            'pagos' => Pago::with(['venta', 'metodoPago'])
                ->where('nro_pago', 'ilike', "%{$query}%")
                ->orWhere('observaciones', 'ilike', "%{$query}%")
                ->limit(10)
                ->get(),
        ];

        return Inertia::render('Busqueda/Resultados', [
            'query' => $query,
            'resultados' => $resultados,
        ]);
    }
}

