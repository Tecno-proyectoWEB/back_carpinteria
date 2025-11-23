<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;

class MetodoPagoController extends Controller
{
    public function index()
    {
        return response()->json(MetodoPago::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago = MetodoPago::create($request->all());
        return response()->json($metodoPago, 201);
    }

    public function show(MetodoPago $metodoPago)
    {
        return response()->json($metodoPago);
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago->update($request->all());
        return response()->json($metodoPago);
    }

    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();
        return response()->json(null, 204);
    }
}
