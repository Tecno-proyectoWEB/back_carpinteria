<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Material;
use App\Models\Venta;
use App\Models\Pago;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
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

