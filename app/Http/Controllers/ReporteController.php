<?php

namespace App\Http\Controllers;

use App\Models\Venta;
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

        // Si no hay fechas, usar valores por defecto (mes actual)
        $fechaInicio = $request->fecha_inicio ?? now()->startOfMonth()->toDateString();
        $fechaFin = $request->fecha_fin ?? now()->toDateString();

        // Validar solo si se proporcionan fechas
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'usuario_id' => 'nullable|integer|exists:usuario,id',
            ]);
            $fechaInicio = $request->fecha_inicio;
            $fechaFin = $request->fecha_fin;
        }

        $query = Venta::with(['usuario', 'metodoPago'])
            ->where('estado', true)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        $ventas = $query->orderBy('fecha', 'desc')->get();

        $totalVentas = (float) $ventas->sum('importe_total');
        $totalVentasContado = (float) $ventas->filter(function($venta) {
            return $venta->pagos()->where('tipo', 'CONTADO')->exists();
        })->sum('importe_total');
        $totalVentasCredito = (float) $ventas->filter(function($venta) {
            return $venta->pagos()->where('tipo', 'CREDITO')->exists();
        })->sum('importe_total');

        return Inertia::render('Reportes/Ventas', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'totalVentasContado' => $totalVentasContado,
            'totalVentasCredito' => $totalVentasCredito,
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'usuario_id' => $request->usuario_id ?? '',
            ],
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
        $totalVentas = (float) Venta::where('estado', true)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->sum('importe_total');

        $ventasPorVendedor = Venta::select('usuario_id', DB::raw('sum(importe_total) as total'))
            ->where('estado', true)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->with('usuario:id,nombre,apellido')
            ->groupBy('usuario_id')
            ->get()
            ->map(function($item) {
                $item->total = (float) $item->total;
                return $item;
            });

        // Productos más vendidos
        $productosMasVendidos = DB::table('detalle_venta as dv')
            ->join('venta as v', 'dv.venta_id', '=', 'v.id')
            ->join('producto as pr', 'dv.producto_id', '=', 'pr.id')
            ->select('pr.id', 'pr.nombre', DB::raw('sum(dv.cantidad) as total_cantidad'), DB::raw('sum(dv.importe_total) as total_ventas'))
            ->where('v.estado', true)
            ->whereBetween('v.fecha', [$fechaInicio, $fechaFin])
            ->whereNotNull('dv.producto_id')
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        // Servicios más vendidos
        $serviciosMasVendidos = DB::table('detalle_venta as dv')
            ->join('venta as v', 'dv.venta_id', '=', 'v.id')
            ->join('servicio as s', 'dv.servicio_id', '=', 's.id')
            ->select('s.id', 's.nombre', DB::raw('count(*) as total_ventas'), DB::raw('sum(dv.importe_total) as total_ingresos'))
            ->where('v.estado', true)
            ->whereBetween('v.fecha', [$fechaInicio, $fechaFin])
            ->whereNotNull('dv.servicio_id')
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
            'totalVentas' => (float) $totalVentas,
            'ventasPorVendedor' => $ventasPorVendedor,
            'productosMasVendidos' => $productosMasVendidos->map(function($item) {
                $item->total_ventas = (float) ($item->total_ventas ?? 0);
                return $item;
            }),
            'serviciosMasVendidos' => $serviciosMasVendidos->map(function($item) {
                $item->total_ingresos = (float) ($item->total_ingresos ?? 0);
                return $item;
            }),
            'pagosPendientes' => (int) $pagosPendientes,
            'pagosPagados' => (float) ($pagosPagados ?? 0),
            'productosBajoStock' => (int) $productosBajoStock,
            'materialesBajoStock' => (int) $materialesBajoStock,
            'ingresosInventario' => (int) $ingresosInventario,
            'salidasInventario' => (int) $salidasInventario,
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

        $movimientos = MovimientoInventario::with(['material', 'producto', 'usuario', 'venta'])
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

