<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Controllers\MenuController;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\Compra;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        \Log::debug('DashboardController::index() ejecutándose', [
            'path' => $request->path(),
            'auth_check' => \Illuminate\Support\Facades\Auth::check(),
            'user_id' => $request->user() ? $request->user()->id : null,
            'session_id' => $request->session()->getId(),
        ]);
        
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        
        // Obtener contador de visitas para esta página
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        // Estadísticas del negocio
        $estadisticas = $this->obtenerEstadisticas($request->user());
        
        // Compartir usuario autenticado (solución temporal porque el middleware no se ejecuta)
        $this->shareAuthUser($request);

        return Inertia::render('Dashboard', [
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'estadisticas' => $estadisticas,
        ]);
    }

    private function obtenerEstadisticas($user)
    {
        $rol = $user->rol->nombre ?? '';

        // Estadísticas generales (todos los roles)
        $estadisticas = [
            'resumen' => $this->obtenerResumen(),
            'ventas_recientes' => [],
            'productos_mas_vendidos' => [],
            'alertas_stock' => [],
        ];

        // Estadísticas según rol
        switch ($rol) {
            case 'PROPIETARIO':
            case 'SECRETARIA':
                $estadisticas['ventas_recientes'] = $this->obtenerVentasRecientes();
                $estadisticas['ventas_diarias'] = $this->obtenerVentasDiarias();
                $estadisticas['compras_recientes'] = $this->obtenerComprasRecientes();
                $estadisticas['productos_mas_vendidos'] = $this->obtenerProductosMasVendidos();
                $estadisticas['alertas_stock'] = $this->obtenerAlertasStock();
                $estadisticas['actividad_usuarios'] = $this->obtenerActividadUsuarios();
                break;

            case 'CARPINTERO':
                $estadisticas['alertas_stock'] = $this->obtenerAlertasStock();
                $estadisticas['productos_mas_vendidos'] = $this->obtenerProductosMasVendidos();
                break;

            case 'CLIENTE':
                $estadisticas['mis_pedidos'] = $this->obtenerMisPedidos($user->id);
                break;

            case 'PROVEEDOR':
                $estadisticas['mis_compras'] = $this->obtenerMisCompras($user->id);
                break;
        }

        return $estadisticas;
    }

    private function obtenerResumen()
    {
        return [
            'total_productos' => Producto::count(),
            'total_servicios' => Servicio::where('activo', true)->count(),
            'total_usuarios' => Usuario::where('estado', true)->count(),
            'total_pedidos' => Pedido::where('estado', true)->count(),
            'ventas_mes' => Pedido::where('estado', true)
                ->whereMonth('fecha', now()->month)
                ->whereYear('fecha', now()->year)
                ->sum('importe_total_desc') ?? 0,
        ];
    }

    private function obtenerVentasRecientes($limit = 5)
    {
        return Pedido::where('estado', true)
            ->with(['usuario', 'metodoPago'])
            ->orderBy('fecha', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($pedido) {
                return [
                    'id' => $pedido->id,
                    'fecha' => $pedido->fecha,
                    'cliente' => $pedido->usuario->nombre . ' ' . $pedido->usuario->apellido,
                    'total' => $pedido->importe_total_desc,
                    'metodo_pago' => $pedido->metodoPago->nombre ?? 'N/A',
                ];
            });
    }

    private function obtenerVentasDiarias($dias = 7)
    {
        return DB::table('v_ventas_diarias')
            ->where('fecha', '>=', now()->subDays($dias))
            ->orderBy('fecha', 'desc')
            ->get();
    }

    private function obtenerComprasRecientes($limit = 5)
    {
        return Compra::where('estado', 'COMPLETADA')
            ->with('proveedor')
            ->orderBy('fecha', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($compra) {
                return [
                    'id' => $compra->id,
                    'fecha' => $compra->fecha,
                    'proveedor' => $compra->proveedor->nombre ?? 'N/A',
                    'total' => $compra->importe_total,
                ];
            });
    }

    private function obtenerProductosMasVendidos($limit = 5)
    {
        return DB::table('detalle_pedido as dp')
            ->join('producto as p', 'dp.producto_id', '=', 'p.id')
            ->join('pedido as ped', 'dp.pedido_id', '=', 'ped.id')
            ->where('ped.estado', true)
            ->select('p.id', 'p.nombre', DB::raw('SUM(dp.cantidad) as total_vendido'))
            ->groupBy('p.id', 'p.nombre')
            ->orderBy('total_vendido', 'desc')
            ->limit($limit)
            ->get();
    }

    private function obtenerAlertasStock()
    {
        $productos = DB::table('v_stock_bajo_productos')->get();
        $materiales = DB::table('v_stock_bajo_materiales')->get();

        return [
            'productos' => $productos,
            'materiales' => $materiales,
            'total' => $productos->count() + $materiales->count(),
        ];
    }

    private function obtenerActividadUsuarios($limit = 5)
    {
        return DB::table('v_actividad_usuarios')
            ->orderBy('ultima_actividad', 'desc')
            ->limit($limit)
            ->get();
    }

    private function obtenerMisPedidos($usuarioId)
    {
        return Pedido::where('usuario_id', $usuarioId)
            ->with('metodoPago')
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();
    }

    private function obtenerMisCompras($usuarioId)
    {
        // Asumiendo que el proveedor está relacionado con el usuario
        return Compra::whereHas('proveedor', function ($query) use ($usuarioId) {
            // Esta relación necesita ser definida en el modelo
        })
            ->orderBy('fecha', 'desc')
            ->limit(5)
            ->get();
    }
}

