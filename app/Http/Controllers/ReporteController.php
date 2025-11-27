<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Material;
use App\Models\Pago;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReporteController extends Controller
{
    public function index()
    {
        if (!Auth::user()->tienePermiso('reportes.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver reportes']);
        }

        return Inertia::render('Reportes/Index');
    }

    public function ventas(Request $request)
    {
        if (!Auth::user()->tienePermiso('reportes.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver reportes']);
        }

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'usuario_id' => 'nullable|integer|exists:usuario,id',
        ]);

        $query = Pedido::with(['usuario', 'metodoPago'])
            ->where('estado', true)
            ->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        $ventas = $query->orderBy('fecha', 'desc')->get();

        $totalVentas = $ventas->sum('importe_total');
        $totalVentasContado = $ventas->filter(function($venta) {
            return $venta->pagos()->where('tipo', 'CONTADO')->exists();
        })->sum('importe_total');
        $totalVentasCredito = $ventas->filter(function($venta) {
            return $venta->pagos()->where('tipo', 'CREDITO')->exists();
        })->sum('importe_total');

        return Inertia::render('Reportes/Ventas', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'totalVentasContado' => $totalVentasContado,
            'totalVentasCredito' => $totalVentasCredito,
            'filters' => $request->only(['fecha_inicio', 'fecha_fin', 'usuario_id']),
        ]);
    }

    public function estadisticas(Request $request)
    {
        if (!Auth::user()->tienePermiso('reportes.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver reportes']);
        }

        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->fecha_fin ?? now()->toDateString();

        // Estadísticas de ventas
        $totalVentas = Pedido::where('estado', true)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('importe_total');

        $ventasPorVendedor = Pedido::select('usuario_id', DB::raw('sum(importe_total) as total'))
            ->where('estado', true)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with('usuario:id,nombre,apellido')
            ->groupBy('usuario_id')
            ->get();

        // Productos más vendidos
        $productosMasVendidos = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->select('pr.id', 'pr.nombre', DB::raw('sum(dp.cantidad) as total_cantidad'), DB::raw('sum(dp.importe_total) as total_ventas'))
            ->where('p.estado', true)
            ->whereBetween('p.fecha', [$fechaInicio, $fechaFin])
            ->whereNotNull('dp.producto_id')
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        // Servicios más vendidos
        $serviciosMasVendidos = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('servicio as s', 'dp.servicio_id', '=', 's.id')
            ->select('s.id', 's.nombre', DB::raw('count(*) as total_ventas'), DB::raw('sum(dp.importe_total) as total_ingresos'))
            ->where('p.estado', true)
            ->whereBetween('p.fecha', [$fechaInicio, $fechaFin])
            ->whereNotNull('dp.servicio_id')
            ->groupBy('s.id', 's.nombre')
            ->orderByDesc('total_ventas')
            ->limit(10)
            ->get();

        // Estadísticas de pagos
        $pagosPendientes = Pago::where('estado', 'PENDIENTE')->count();
        $pagosPagados = Pago::where('estado', 'PAGADO')
            ->whereBetween('fecha_pago', [$fechaInicio, $fechaFin])
            ->sum('monto');

        // Estadísticas de inventario
        $productosBajoStock = Producto::whereColumn('stock', '<=', 'stock_minimo')->count();
        $materialesBajoStock = Material::whereColumn('stock_actual', '<=', 'stock_minimo')->count();

        // Movimientos de inventario
        $ingresosInventario = MovimientoInventario::where('tipo', 'INGRESO')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->count();
        $salidasInventario = MovimientoInventario::where('tipo', 'SALIDA')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->count();

        return Inertia::render('Reportes/Estadisticas', [
            'totalVentas' => $totalVentas,
            'ventasPorVendedor' => $ventasPorVendedor,
            'productosMasVendidos' => $productosMasVendidos,
            'serviciosMasVendidos' => $serviciosMasVendidos,
            'pagosPendientes' => $pagosPendientes,
            'pagosPagados' => $pagosPagados,
            'productosBajoStock' => $productosBajoStock,
            'materialesBajoStock' => $materialesBajoStock,
            'ingresosInventario' => $ingresosInventario,
            'salidasInventario' => $salidasInventario,
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }

    public function inventario(Request $request)
    {
        if (!Auth::user()->tienePermiso('reportes.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver reportes']);
        }

        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->fecha_fin ?? now()->toDateString();

        $movimientos = MovimientoInventario::with(['material', 'producto', 'usuario', 'pedido'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'desc')
            ->get();

        $ingresos = $movimientos->where('tipo', 'INGRESO');
        $salidas = $movimientos->where('tipo', 'SALIDA');

        return Inertia::render('Reportes/Inventario', [
            'movimientos' => $movimientos,
            'ingresos' => $ingresos,
            'salidas' => $salidas,
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ],
        ]);
    }
}

