<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            abort(403, 'No tiene permiso para ver pagos');
        }

        $query = Pago::with(['pedido.usuario', 'metodoPago', 'usuario']);

        // Filtros
        if ($request->has('pedido_id') && $request->pedido_id) {
            $query->where('pedido_id', $request->pedido_id);
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha_pago', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha_pago', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('pedido', function($q) use ($search) {
                    $q->where('id', 'ILIKE', "%{$search}%");
                })
                ->orWhere('observaciones', 'ILIKE', "%{$search}%");
            });
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')
            ->orderBy('fecha_vencimiento', 'asc')
            ->paginate($request->get('per_page', 20));

        $pedidos = Pedido::where('estado', true)->orderBy('id', 'desc')->limit(100)->get();
        $metodosPago = MetodoPago::all();

        // Estadísticas
        $totalPendientes = Pago::where('estado', 'PENDIENTE')->sum('monto');
        $totalPagados = Pago::where('estado', 'PAGADO')
            ->whereMonth('fecha_pago', now()->month)
            ->sum('monto');
        $totalVencidos = Pago::where('estado', 'VENCIDO')->count();

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'pedidos' => $pedidos,
            'metodosPago' => $metodosPago,
            'estadisticas' => [
                'total_pendientes' => $totalPendientes,
                'total_pagados_mes' => $totalPagados,
                'total_vencidos' => $totalVencidos,
            ],
            'filters' => $request->only(['pedido_id', 'estado', 'tipo', 'fecha_desde', 'fecha_hasta', 'search']),
        ]);
    }

    public function show(Pago $pago)
    {
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            abort(403, 'No tiene permiso para ver pagos');
        }

        $pago->load(['pedido.usuario', 'pedido.detalles.producto', 'pedido.detalles.servicio', 'metodoPago', 'usuario']);
        $metodosPago = MetodoPago::all();

        return Inertia::render('Pagos/Show', [
            'pago' => $pago,
            'metodosPago' => $metodosPago,
        ]);
    }

    public function registrarPago(Request $request, Pago $pago)
    {
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            abort(403, 'No tiene permiso para registrar pagos');
        }

        if ($pago->estado === 'PAGADO') {
            return back()->withErrors(['error' => 'El pago ya está registrado']);
        }

        $validated = $request->validate([
            'metodo_pago_id' => 'sometimes|exists:metodo_pago,id',
            'observaciones' => 'nullable|string',
        ]);

        $datosAnteriores = $pago->toArray();

        DB::beginTransaction();
        try {
            $pago->update([
                'estado' => 'PAGADO',
                'fecha_pago' => now(),
                'usuario_id' => Auth::id(),
                'metodo_pago_id' => $validated['metodo_pago_id'] ?? $pago->metodo_pago_id,
                'observaciones' => $validated['observaciones'] ?? $pago->observaciones,
            ]);

            // Verificar si todas las cuotas están pagadas para confirmar el pedido
            $pedido = $pago->pedido;
            if ($pedido && !$pedido->estado) {
                $pagosPendientes = $pedido->pagos()->where('estado', '!=', 'PAGADO')->count();
                if ($pagosPendientes === 0) {
                    $pedido->estado = true;
                    $pedido->save();

                    // Generar movimientos de inventario
                    foreach ($pedido->detalles as $detalle) {
                        if ($detalle->producto_id && $detalle->estado === false) {
                            $producto = \App\Models\Producto::findOrFail($detalle->producto_id);
                            
                            if ($producto->stock >= $detalle->cantidad) {
                                $producto->stock -= $detalle->cantidad;
                                $producto->save();

                                \App\Models\MovimientoInventario::create([
                                    'tipo' => 'SALIDA',
                                    'cantidad' => $detalle->cantidad,
                                    'motivo' => 'Venta a crédito - Todas las cuotas pagadas',
                                    'observaciones' => "Pedido #{$pedido->id} - {$producto->nombre}",
                                    'material_id' => null,
                                    'producto_id' => $producto->id,
                                    'compra_id' => null,
                                    'pedido_id' => $pedido->id,
                                    'usuario_id' => Auth::id(),
                                    'fecha' => now(),
                                ]);

                                $detalle->estado = true;
                                $detalle->save();
                            }
                        }
                    }
                }
            }

            DB::commit();

            \App\Models\Bitacora::create([
                'accion' => 'Pago registrado',
                'modulo' => 'Pago',
                'tabla_afectada' => 'pago',
                'registro_id' => $pago->id,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $pago->toArray(),
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            return redirect()->route('pagos.show', $pago->id)
                ->with('success', 'Pago registrado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el pago: ' . $e->getMessage()]);
        }
    }
}

