<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Material;
use App\Models\Venta;
use App\Models\Pago;
use App\Models\Visita;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $productosStockBajo = Producto::whereColumn('stock', '<=', 'stock_minimo')->get();
        $materialesStockBajo = Material::whereColumn('stock_actual', '<=', 'stock_minimo')->get();

        // Estadísticas del negocio
        $ventasHoy = Venta::whereDate('fecha', today())->sum('importe_total');
        $ventasMes = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('importe_total');

        $ventasHoyCount = Venta::whereDate('fecha', today())->count();
        $ventasMesCount = Venta::whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->count();

        // Estadísticas de acceso
        $visitasHoy = Visita::whereDate('created_at', today())->count();
        $visitasMes = Visita::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $usuariosActivos = Usuario::where('estado', true)->count();

        // Páginas más visitadas
        $paginasMasVisitadas = Visita::select('ruta', DB::raw('count(*) as total'))
            ->groupBy('ruta')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Contar visitas de la página actual
        $visitasPagina = Visita::where('ruta', $request->path())->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProductos' => Producto::count(),
                'totalMateriales' => Material::count(),
                'ventasPendientes' => Venta::where('estado', false)->count(),
                'pagosPendientes' => Pago::where('estado', 'PENDIENTE')->count(),
                'ventasHoy' => $ventasHoy,
                'ventasMes' => $ventasMes,
                'ventasHoyCount' => $ventasHoyCount,
                'ventasMesCount' => $ventasMesCount,
                'visitasHoy' => $visitasHoy,
                'visitasMes' => $visitasMes,
                'usuariosActivos' => $usuariosActivos,
            ],
            'productosStockBajo' => $productosStockBajo,
            'materialesStockBajo' => $materialesStockBajo,
            'paginasMasVisitadas' => $paginasMasVisitadas,
            'visitasPagina' => $visitasPagina,
        ]);
    }
}

