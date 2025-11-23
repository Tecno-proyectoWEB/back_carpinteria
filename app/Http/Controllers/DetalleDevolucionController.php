<?php

namespace App\Http\Controllers;

use App\Models\DetalleDevolucion;
use Illuminate\Http\Request;

class DetalleDevolucionController extends Controller
{
    public function index()
    {
        return response()->json(DetalleDevolucion::with(['devolucion', 'detallePedido'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'cantidad' => 'nullable|integer|min:1',
            'importe_total' => 'nullable|numeric|min:0',
            'motivo_detalle' => 'nullable|string',
            'devolucion_id' => 'required|exists:devolucion,id',
            'detalle_pedido_id' => 'required|exists:detalle_pedido,id',
        ]);

        $detalle = DetalleDevolucion::create($request->all());
        return response()->json($detalle->load(['devolucion', 'detallePedido']), 201);
    }

    public function show(DetalleDevolucion $detalleDevolucion)
    {
        return response()->json($detalleDevolucion->load(['devolucion', 'detallePedido']));
    }

    public function update(Request $request, DetalleDevolucion $detalleDevolucion)
    {
        $request->validate([
            'cantidad' => 'nullable|integer|min:1',
            'importe_total' => 'nullable|numeric|min:0',
            'motivo_detalle' => 'nullable|string',
            'devolucion_id' => 'sometimes|required|exists:devolucion,id',
            'detalle_pedido_id' => 'sometimes|required|exists:detalle_pedido,id',
        ]);

        $detalleDevolucion->update($request->all());
        return response()->json($detalleDevolucion->load(['devolucion', 'detallePedido']));
    }

    public function destroy(DetalleDevolucion $detalleDevolucion)
    {
        $detalleDevolucion->delete();
        return response()->json(null, 204);
    }
}
