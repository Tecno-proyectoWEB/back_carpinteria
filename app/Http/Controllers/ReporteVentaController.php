<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class ReporteVentaController extends Controller
{
    // Obtener reporte de ventas con filtros
    public function reporteVentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'usuario_id' => 'nullable|integer|exists:usuario,id',
        ]);

        $query = DB::table('pedido as p')
            ->join('usuario as u', 'p.usuario_id', '=', 'u.id')
            ->select('p.fecha', 'p.importe_total', 'u.nombre as vendedor')
            ->where('p.estado', 'completado')
            ->whereBetween('p.fecha', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->filled('usuario_id')) {
            $query->where('p.usuario_id', $request->usuario_id);
        }

        $ventas = $query->get();

        return response()->json($ventas);
    }

    // Resumen ventas: total, ventas por vendedor, productos más vendidos
    public function resumenVentas(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $totalVentas = DB::table('pedido')
            ->where('estado', 'completado')
            ->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->sum('importe_total');

        $ventasPorVendedor = DB::table('pedido as p')
            ->join('usuario as u', 'p.usuario_id', '=', 'u.id')
            ->select('u.nombre', DB::raw('sum(p.importe_total) as total'))
            ->where('p.estado', 'completado')
            ->whereBetween('p.fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->groupBy('u.nombre')
            ->get();

        $productosMasVendidos = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->select('pr.nombre', DB::raw('sum(dp.cantidad) as total_cantidad'))
            ->where('p.estado', 'completado')
            ->whereBetween('p.fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->groupBy('pr.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        $resumen = [
            'total_ventas' => $totalVentas,
            'ventas_por_vendedor' => $ventasPorVendedor,
            'productos_mas_vendidos' => $productosMasVendidos,
        ];

        return response()->json($resumen);
    }

    // Exportar reporte ventas a PDF
    public function exportarPdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'usuario_id' => 'nullable|integer|exists:usuario,id',
        ]);

        $query = DB::table('pedido as p')
            ->join('usuario as u', 'p.usuario_id', '=', 'u.id')
            ->select('p.fecha', 'p.importe_total', 'u.nombre as vendedor')
            ->where('p.estado', 'completado')
            ->whereBetween('p.fecha', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->filled('usuario_id')) {
            $query->where('p.usuario_id', $request->usuario_id);
        }

        $ventas = $query->get();

        $pdf = PDF::loadView('reportes.ventas_pdf', ['ventas' => $ventas]);

        return $pdf->download('reporte_ventas.pdf');
    }
}
