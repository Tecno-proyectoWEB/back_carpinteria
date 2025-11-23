<?php

namespace App\Http\Controllers;

use App\Models\DetallePedido;
use Illuminate\Http\Request;

class DetallePedidoController extends Controller
{
    public function index()
    {
        return response()->json(DetallePedido::with(['pedido', 'producto'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'producto_id' => 'nullable|exists:producto,id',
            'pedido_id' => 'nullable|exists:pedido,id',
            'cantidad' => 'required|integer|min:1',
            'estado' => 'boolean',
            'importe_total' => 'nullable|numeric|min:0',
            'importe_total_desc' => 'nullable|numeric|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $detalle = DetallePedido::create($request->all());
        return response()->json($detalle->load(['pedido', 'producto']), 201);
    }

    public function show(DetallePedido $detallePedido)
    {
        return response()->json($detallePedido->load(['pedido', 'producto']));
    }

    public function update(Request $request, DetallePedido $detallePedido)
    {
        $request->validate([
            'producto_id' => 'nullable|exists:producto,id',
            'pedido_id' => 'nullable|exists:pedido,id',
            'cantidad' => 'sometimes|required|integer|min:1',
            'estado' => 'boolean',
            'importe_total' => 'nullable|numeric|min:0',
            'importe_total_desc' => 'nullable|numeric|min:0',
            'precio_unitario' => 'nullable|numeric|min:0',
        ]);

        $detallePedido->update($request->all());
        return response()->json($detallePedido->load(['pedido', 'producto']));
    }

    public function destroy(DetallePedido $detallePedido)
    {
        $detallePedido->delete();
        return response()->json(null, 204);
    }
}
