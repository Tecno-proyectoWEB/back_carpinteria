<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            return response()->json(['message' => 'No tiene permiso para ver pagos'], 403);
        }

        $query = Pago::with(['pedido', 'metodoPago', 'usuario']);

        if ($request->has('pedido_id')) {
            $query->where('pedido_id', $request->pedido_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json($query->orderBy('fecha_pago', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return response()->json(['message' => 'No tiene permiso para registrar pagos'], 403);
        }

        $request->validate([
            'monto' => 'required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
            'estado' => 'required|in:PENDIENTE,PAGADO,VENCIDO,CANCELADO',
            'tipo' => 'required|in:CONTADO,CREDITO,CUOTA',
            'numero_cuota' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'pedido_id' => 'required|exists:pedido,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
        ]);

        $pago = Pago::create([
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago ?? ($request->estado === 'PAGADO' ? now() : null),
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
            'tipo' => $request->tipo,
            'numero_cuota' => $request->numero_cuota,
            'observaciones' => $request->observaciones,
            'pedido_id' => $request->pedido_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'usuario_id' => Auth::id(),
        ]);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Pago creado',
            'modulo' => 'Pago',
            'tabla_afectada' => 'pago',
            'registro_id' => $pago->id,
            'datos_nuevos' => $pago->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($pago->load(['pedido', 'metodoPago', 'usuario']), 201);
    }

    public function show(Pago $pago)
    {
        return response()->json($pago->load(['pedido', 'metodoPago', 'usuario']));
    }

    public function update(Request $request, Pago $pago)
    {
        $request->validate([
            'monto' => 'sometimes|required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'estado' => 'sometimes|required|in:PENDIENTE,PAGADO,VENCIDO,CANCELADO',
            'tipo' => 'sometimes|required|in:CONTADO,CREDITO,CUOTA',
            'numero_cuota' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'metodo_pago_id' => 'sometimes|required|exists:metodo_pago,id',
        ]);

        // Si se marca como PAGADO y no tiene fecha_pago, asignarla
        if ($request->estado === 'PAGADO' && !$pago->fecha_pago) {
            $request->merge(['fecha_pago' => now()]);
        }

        $pago->update($request->all());
        return response()->json($pago->load(['pedido', 'metodoPago', 'usuario']));
    }

    public function registrarPago(Request $request, Pago $pago)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return response()->json(['message' => 'No tiene permiso para registrar pagos'], 403);
        }

        if ($pago->estado === 'PAGADO') {
            return response()->json(['error' => 'El pago ya está registrado'], 400);
        }

        $datos_anteriores = $pago->toArray();
        $pago->update([
            'estado' => 'PAGADO',
            'fecha_pago' => now(),
            'usuario_id' => Auth::id(),
        ]);

        // Verificar si todas las cuotas están pagadas para confirmar el pedido
        $pedido = $pago->pedido;
        if ($pedido && !$pedido->estado) {
            $pagosPendientes = $pedido->pagos()->where('estado', '!=', 'PAGADO')->count();
            if ($pagosPendientes === 0) {
                // Todas las cuotas pagadas, confirmar pedido
                $pedido->estado = true;
                $pedido->save();

                // Generar movimientos de inventario si aún no existen
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->producto_id && $detalle->estado === false) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        
                        if ($producto->stock >= $detalle->cantidad) {
                            $producto->stock -= $detalle->cantidad;
                            $producto->save();

                            MovimientoInventario::create([
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

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Pago registrado',
            'modulo' => 'Pago',
            'tabla_afectada' => 'pago',
            'registro_id' => $pago->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $pago->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($pago->load(['pedido', 'metodoPago', 'usuario']));
    }
}
