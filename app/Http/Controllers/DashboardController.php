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

        $productosStockBajo = Producto::whereColumn('stock', '<=', 'stock_minimo')->get();
        $materialesStockBajo = Material::whereColumn('stock_actual', '<=', 'stock_minimo')->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProductos' => Producto::count(),
                'totalMateriales' => Material::count(),
                'ventasPendientes' => Venta::where('estado', false)->count(),
                'pagosPendientes' => Pago::where('estado', 'PENDIENTE')->count(),
            ],
            'productosStockBajo' => $productosStockBajo,
            'materialesStockBajo' => $materialesStockBajo,
        ]);
    }
}

