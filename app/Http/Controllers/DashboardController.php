<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Material;
use App\Models\Venta;
use App\Models\Pago;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        // Logging para diagnosticar problemas de autenticación
        Log::info('DashboardController::index', [
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => request()->session()->getId(),
            'has_session' => request()->hasSession(),
        ]);

        $productosStockBajo = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();
        $materialesStockBajo = Material::whereColumn('stock_actual', '<=', 'stock_minimo')->count();

        return Inertia::render('Dashboard', [
            'estadisticas' => [
                'resumen' => [
                    'total_productos' => Producto::count(),
                    'total_servicios' => \App\Models\Servicio::count(),
                    'total_usuarios' => \App\Models\Usuario::count(),
                    'ventas_mes' => (float) (Venta::whereMonth('fecha', now()->month)
                        ->whereYear('fecha', now()->year)
                        ->sum('importe_total') ?? 0),
                ],
                'ventas_recientes' => Venta::with(['metodo_pago', 'usuario'])
                    ->orderBy('fecha', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($venta) {
                        return [
                            'id' => $venta->id,
                            'fecha' => $venta->fecha,
                            'cliente' => $venta->usuario->nombre ?? 'Cliente general',
                            'total' => $venta->importe_total,
                            'metodo_pago' => $venta->metodo_pago->nombre ?? 'N/A',
                        ];
                    }),
                'alertas_stock' => [
                    'total' => $productosStockBajo + $materialesStockBajo,
                ],
                'productos_mas_vendidos' => \Illuminate\Support\Facades\DB::table('detalle_venta')
                    ->join('producto', 'detalle_venta.producto_id', '=', 'producto.id')
                    ->select('producto.id', 'producto.nombre', \Illuminate\Support\Facades\DB::raw('SUM(detalle_venta.cantidad) as total_vendido'))
                    ->groupBy('producto.id', 'producto.nombre')
                    ->orderByDesc('total_vendido')
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}

