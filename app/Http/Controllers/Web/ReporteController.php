<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Usuario;
use App\Models\Proveedor;

class ReporteController extends Controller
{
    use HasPermissions;
    public function index()
    {
        $this->autorizarPermiso('reportes.ver', 'No tiene permiso para ver reportes');

        return Inertia::render('Reportes/Index');
    }

    public function ventas(Request $request)
    {
        $this->autorizarPermiso('reportes.ver', 'No tiene permiso para ver reportes');

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        // Validar fechas
        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'usuario_id' => 'nullable|integer|exists:usuario,id',
        ]);

        // Reporte de ventas detallado
        $query = DB::table('pedido as p')
            ->join('usuario as u', 'p.usuario_id', '=', 'u.id')
            ->leftJoin('metodo_pago as mp', 'p.metodo_pago_id', '=', 'mp.id')
            ->select(
                'p.id',
                'p.fecha',
                'p.importe_total',
                'p.importe_total_desc',
                'u.nombre as vendedor_nombre',
                'u.apellido as vendedor_apellido',
                'mp.nombre as metodo_pago'
            )
            ->where('p.estado', true)
            ->whereBetween('p.fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('usuario_id')) {
            $query->where('p.usuario_id', $request->usuario_id);
        }

        $ventas = $query->orderBy('p.fecha', 'desc')->get();

        // Resumen de ventas
        $totalVentas = $ventas->sum('importe_total_desc');
        $totalPedidos = $ventas->count();
        $promedioVenta = $totalPedidos > 0 ? $totalVentas / $totalPedidos : 0;

        // Ventas por vendedor
        $ventasPorVendedor = $ventas->groupBy(function ($venta) {
            return $venta->vendedor_nombre . ' ' . $venta->vendedor_apellido;
        })->map(function ($grupo) {
            return [
                'vendedor' => $grupo->first()->vendedor_nombre . ' ' . $grupo->first()->vendedor_apellido,
                'total' => $grupo->sum('importe_total_desc'),
                'cantidad' => $grupo->count(),
            ];
        })->values();

        // Ventas por método de pago
        $ventasPorMetodoPago = $ventas->groupBy('metodo_pago')->map(function ($grupo, $metodo) {
            return [
                'metodo' => $metodo ?: 'Sin método',
                'total' => $grupo->sum('importe_total_desc'),
                'cantidad' => $grupo->count(),
            ];
        })->values();

        // Ventas diarias (últimos 30 días)
        $ventasDiarias = DB::table('v_ventas_diarias')
            ->where('fecha', '>=', now()->subDays(30))
            ->orderBy('fecha', 'desc')
            ->get();

        // Productos más vendidos
        $productosMasVendidos = DB::table('detalle_pedido as dp')
            ->join('pedido as p', 'dp.pedido_id', '=', 'p.id')
            ->join('producto as pr', 'dp.producto_id', '=', 'pr.id')
            ->select('pr.id', 'pr.nombre', DB::raw('SUM(dp.cantidad) as total_cantidad'), DB::raw('SUM(dp.importe_total) as total_ventas'))
            ->where('p.estado', true)
            ->whereBetween('p.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('pr.id', 'pr.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        $usuarios = Usuario::where('estado', true)->orderBy('nombre')->get();

        return Inertia::render('Reportes/Ventas', [
            'ventas' => $ventas,
            'resumen' => [
                'total_ventas' => $totalVentas,
                'total_pedidos' => $totalPedidos,
                'promedio_venta' => $promedioVenta,
            ],
            'ventas_por_vendedor' => $ventasPorVendedor,
            'ventas_por_metodo_pago' => $ventasPorMetodoPago,
            'ventas_diarias' => $ventasDiarias,
            'productos_mas_vendidos' => $productosMasVendidos,
            'usuarios' => $usuarios,
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'usuario_id' => $request->usuario_id,
            ],
        ]);
    }

    public function compras(Request $request)
    {
        $this->autorizarPermiso('reportes.ver', 'No tiene permiso para ver reportes');

        $fechaInicio = $request->get('fecha_inicio', now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', now()->format('Y-m-d'));

        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'proveedor_id' => 'nullable|integer|exists:proveedor,id',
        ]);

        // Reporte de compras detallado
        $query = DB::table('compra as c')
            ->join('proveedor as p', 'c.proveedor_id', '=', 'p.id')
            ->join('usuario as u', 'c.usuario_id', '=', 'u.id')
            ->select(
                'c.id',
                'c.fecha',
                'c.importe_total',
                'c.estado',
                'p.nombre as proveedor',
                'u.nombre as registrado_por_nombre',
                'u.apellido as registrado_por_apellido'
            )
            ->where('c.estado', 'COMPLETADA')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin]);

        if ($request->filled('proveedor_id')) {
            $query->where('c.proveedor_id', $request->proveedor_id);
        }

        $compras = $query->orderBy('c.fecha', 'desc')->get();

        // Resumen de compras
        $totalCompras = $compras->sum('importe_total');
        $totalRegistros = $compras->count();
        $promedioCompra = $totalRegistros > 0 ? $totalCompras / $totalRegistros : 0;

        // Compras por proveedor
        $comprasPorProveedor = $compras->groupBy('proveedor')->map(function ($grupo, $proveedor) {
            return [
                'proveedor' => $proveedor,
                'total' => $grupo->sum('importe_total'),
                'cantidad' => $grupo->count(),
            ];
        })->values();

        // Materiales más comprados
        $materialesMasComprados = DB::table('detalle_pedido_compra as dpc')
            ->join('compra as c', 'dpc.compra_id', '=', 'c.id')
            ->join('material as m', 'dpc.material_id', '=', 'm.id')
            ->select('m.id', 'm.nombre', DB::raw('SUM(dpc.cantidad) as total_cantidad'), DB::raw('SUM(dpc.importe_total) as total_compras'))
            ->where('c.estado', 'COMPLETADA')
            ->whereBetween('c.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('m.id', 'm.nombre')
            ->orderByDesc('total_cantidad')
            ->limit(10)
            ->get();

        // Compras por proveedor (vista)
        $comprasProveedor = DB::table('v_compras_proveedor')
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();

        return Inertia::render('Reportes/Compras', [
            'compras' => $compras,
            'resumen' => [
                'total_compras' => $totalCompras,
                'total_registros' => $totalRegistros,
                'promedio_compra' => $promedioCompra,
            ],
            'compras_por_proveedor' => $comprasPorProveedor,
            'materiales_mas_comprados' => $materialesMasComprados,
            'compras_proveedor' => $comprasProveedor,
            'proveedores' => $proveedores,
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'proveedor_id' => $request->proveedor_id,
            ],
        ]);
    }

    public function inventario(Request $request)
    {
        $this->autorizarPermiso('reportes.ver', 'No tiene permiso para ver reportes');

        // Stock bajo
        $stockBajoProductos = DB::table('v_stock_bajo_productos')->get();
        $stockBajoMateriales = DB::table('v_stock_bajo_materiales')->get();

        // Movimientos de inventario recientes
        $movimientosRecientes = DB::table('movimiento_inventario as mi')
            ->leftJoin('material as m', 'mi.material_id', '=', 'm.id')
            ->leftJoin('producto as p', 'mi.producto_id', '=', 'p.id')
            ->leftJoin('usuario as u', 'mi.usuario_id', '=', 'u.id')
            ->select(
                'mi.id',
                'mi.tipo',
                'mi.cantidad',
                'mi.fecha',
                'mi.motivo',
                'm.nombre as material_nombre',
                'p.nombre as producto_nombre',
                'u.nombre as usuario_nombre',
                'u.apellido as usuario_apellido'
            )
            ->orderBy('mi.fecha', 'desc')
            ->limit(50)
            ->get();

        // Resumen de movimientos
        $totalIngresos = DB::table('movimiento_inventario')
            ->where('tipo', 'INGRESO')
            ->whereMonth('fecha', now()->month)
            ->sum('cantidad');

        $totalSalidas = DB::table('movimiento_inventario')
            ->where('tipo', 'SALIDA')
            ->whereMonth('fecha', now()->month)
            ->sum('cantidad');

        // Movimientos por tipo
        $movimientosPorTipo = DB::table('movimiento_inventario')
            ->select('tipo', DB::raw('COUNT(*) as cantidad'))
            ->whereMonth('fecha', now()->month)
            ->groupBy('tipo')
            ->get();

        return Inertia::render('Reportes/Inventario', [
            'stock_bajo_productos' => $stockBajoProductos,
            'stock_bajo_materiales' => $stockBajoMateriales,
            'movimientos_recientes' => $movimientosRecientes,
            'resumen' => [
                'total_ingresos' => $totalIngresos,
                'total_salidas' => $totalSalidas,
                'movimientos_por_tipo' => $movimientosPorTipo,
            ],
        ]);
    }
}

