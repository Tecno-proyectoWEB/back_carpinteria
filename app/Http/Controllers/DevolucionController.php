<?php

namespace App\Http\Controllers;

use App\Models\Devolucion;
use App\Models\DetalleDevolucion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class DevolucionController extends Controller
{
    public function index()
    {
        return response()->json(Devolucion::with(['pedido.usuario', 'detalles'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'nullable|date',
            'motivo' => 'nullable|string',
            'pedido_id' => 'required|exists:pedido,id',
        ]);

        $pedido = Pedido::find($request->pedido_id);
        if (!$pedido || !$pedido->estado) {
            return response()->json(['error' => 'Pedido no encontrado o no está activo'], 404);
        }

        $devolucion = Devolucion::create([
            'fecha' => $request->fecha ?? now(),
            'motivo' => $request->motivo,
            'importe_total' => 0,
            'pedido_id' => $pedido->id,
            'usuario_id' => auth()->id(),
            'estado' => false, // pendiente
        ]);

        return response()->json($devolucion->load(['pedido.usuario', 'detalles']), 201);
    }

    public function show(Devolucion $devolucion)
    {
        return response()->json($devolucion->load(['pedido.usuario', 'detalles']));
    }

    public function update(Request $request, Devolucion $devolucion)
    {
        $request->validate([
            'fecha' => 'nullable|date',
            'motivo' => 'nullable|string',
            'pedido_id' => 'sometimes|required|exists:pedido,id',
            'estado' => 'nullable|boolean',
        ]);

        $devolucion->update($request->all());
        return response()->json($devolucion->load(['pedido.usuario', 'detalles']));
    }

    public function destroy(Devolucion $devolucion)
    {
        $devolucion->delete();
        return response()->json(null, 204);
    }

    public function agregarDetalle(Request $request, Devolucion $devolucion)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'importe_total' => 'required|numeric|min:0',
            'motivo_detalle' => 'nullable|string',
            'detalle_pedido_id' => 'required|exists:detalle_pedido,id',
        ]);

        $detalle = DetalleDevolucion::create([
            'cantidad' => $request->cantidad,
            'importe_total' => $request->importe_total,
            'motivo_detalle' => $request->motivo_detalle,
            'devolucion_id' => $devolucion->id,
            'detalle_pedido_id' => $request->detalle_pedido_id,
        ]);

        $this->calcularImporteTotal($devolucion);

        return response()->json($detalle->load(['devolucion', 'detallePedido']), 201);
    }

    protected function calcularImporteTotal(Devolucion $devolucion)
    {
        $importe_total = $devolucion->detalles()->sum('importe_total');
        $devolucion->importe_total = $importe_total;
        $devolucion->save();
    }

    public function aprobarDevolucion(Request $request, Devolucion $devolucion)
    {
        if ($devolucion->estado) {
            return response()->json(['mensaje' => 'Devolución ya aprobada'], 400);
        }

        $devolucion->estado = true;
        $devolucion->save();

        // actualizar stock
        $productos_buen_estado = $request->input('productos_buen_estado', true);
        foreach ($devolucion->detalles as $detalle) {
            $detallePedido = $detalle->detallePedido;
            $producto = $detallePedido->producto;

            if ($productos_buen_estado) {
                $producto->stock += $detalle->cantidad;
            } else {
                $producto->stock -= $detalle->cantidad; // producto dado de baja
            }
            $producto->save();
        }

        // registrar en bitacora
        Bitacora::create([
            'accion' => 'Devolución aprobada',
            'modulo' => 'Devolucion',
            'tabla_afectada' => 'devolucion',
            'registro_id' => $devolucion->id,
            'usuario_id' => auth()->id(),
            'datos_nuevos' => $devolucion->toArray(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->header('User-Agent'),
        ]);

        return response()->json(['mensaje' => 'Devolución aprobada y stock actualizado', 'devolucion' => $devolucion]);
    }
}
