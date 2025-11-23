<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF; // Necesitarás instalar barryvdh/laravel-dompdf para exportar PDF

class ReporteCompraController extends Controller
{
    public function reporteCompras(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'proveedor_id' => 'nullable|integer|exists:proveedor,id',
        ]);

        $query = DB::table('compra as c')
            ->join('proveedor as p', 'c.proveedor_id', '=', 'p.id')
            ->join('usuario as u', 'c.usuario_id', '=', 'u.id')
            ->select('c.fecha', 'c.importe_total', 'p.nombre as proveedor', 'u.nombre as registrado_por')
            ->where('c.estado', 'completada')
            ->whereBetween('c.fecha', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->filled('proveedor_id')) {
            $query->where('c.proveedor_id', $request->proveedor_id);
        }

        $compras = $query->get();

        return response()->json($compras);
    }

    public function resumenCompras(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $totalCompras = DB::table('compra')
            ->where('estado', 'completada')
            ->whereBetween('fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->sum('importe_total');

        $comprasPorProveedor = DB::table('compra as c')
            ->join('proveedor as p', 'c.proveedor_id', '=', 'p.id')
            ->select('p.nombre', DB::raw('sum(c.importe_total) as total'))
            ->where('c.estado', 'completada')
            ->whereBetween('c.fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->groupBy('p.nombre')
            ->get();

        $materialesMasComprados = DB::table('detalle_pedido_compra as dpc')
            ->join('compra as c', 'dpc.compra_id', '=', 'c.id')
            ->join('material as m', 'dpc.material_id', '=', 'm.id')
            ->select('m.nombre', DB::raw('sum(dpc.cantidad) as total_cantidad'))
            ->where('c.estado', 'completada')
            ->whereBetween('c.fecha', [$request->fecha_inicio, $request->fecha_fin])
            ->groupBy('m.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        $resumen = [
            'total_compras' => $totalCompras,
            'compras_por_proveedor' => $comprasPorProveedor,
            'materiales_mas_comprados' => $materialesMasComprados,
        ];

        return response()->json($resumen);
    }

    public function exportarPdf(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'proveedor_id' => 'nullable|integer|exists:proveedor,id',
        ]);

        // Reutiliza la consulta de reporteCompras
        $query = DB::table('compra as c')
            ->join('proveedor as p', 'c.proveedor_id', '=', 'p.id')
            ->join('usuario as u', 'c.usuario_id', '=', 'u.id')
            ->select('c.fecha', 'c.importe_total', 'p.nombre as proveedor', 'u.nombre as registrado_por')
            ->where('c.estado', 'completada')
            ->whereBetween('c.fecha', [$request->fecha_inicio, $request->fecha_fin]);

        if ($request->filled('proveedor_id')) {
            $query->where('c.proveedor_id', $request->proveedor_id);
        }

        $compras = $query->get();

        $pdf = PDF::loadView('reportes.compras_pdf', ['compras' => $compras]);

        return $pdf->download('reporte_compras.pdf');
    }
}
