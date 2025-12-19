<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReporteCompraController extends BaseController
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('reportes.ver')) {
            return $this->respondError('No tiene permiso para ver reportes', 403);
        }

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'proveedor_id' => 'nullable|integer|exists:proveedor,id',
        ]);

        // Reporte de compras detallado
        $query = Compra::with(['proveedor', 'usuario', 'detalles.material'])
            ->where('estado', 'COMPLETADA')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        $compras = $query->orderBy('fecha', 'desc')->get();

        // Resumen de compras
        $totalCompras = $compras->sum('importe_total');
        $totalDescuentos = $compras->sum('importe_descuento');
        $numeroCompras = $compras->count();
        $promedioCompra = $numeroCompras > 0 ? $totalCompras / $numeroCompras : 0;

        // Compras por proveedor
        $comprasPorProveedor = $compras->groupBy('proveedor_id')->map(function ($grupo) {
            $proveedor = $grupo->first()->proveedor;
            return [
                'proveedor' => $proveedor ? $proveedor->nombre : 'Sin proveedor',
                'total_compras' => $grupo->count(),
                'total_importe' => $grupo->sum('importe_total'),
            ];
        })->values();

        // Top 5 materiales más comprados
        $materialesMasComprados = DB::table('detalle_compra as dc')
            ->join('material as m', 'dc.material_id', '=', 'm.id')
            ->join('compra as c', 'dc.compra_id', '=', 'c.id')
            ->where('c.estado', 'COMPLETADA')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->select('m.nombre', DB::raw('SUM(dc.cantidad) as total_cantidad'), DB::raw('SUM(dc.importe_total) as total_importe'))
            ->groupBy('m.id', 'm.nombre')
            ->orderBy('total_cantidad', 'desc')
            ->limit(5)
            ->get();

        $data = [
            'compras' => $compras,
            'resumen' => [
                'total_compras' => $totalCompras,
                'total_descuentos' => $totalDescuentos,
                'numero_compras' => $numeroCompras,
                'promedio_compra' => $promedioCompra,
            ],
            'compras_por_proveedor' => $comprasPorProveedor,
            'materiales_mas_comprados' => $materialesMasComprados,
            'proveedores' => Proveedor::where('activo', true)->get(),
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'proveedor_id' => $request->proveedor_id,
            ],
        ];

        if ($this->isApiRequest($request)) {
            return response()->json($data);
        }

        return $this->respond($data, 'Reportes/Compras', $data);
    }
}
