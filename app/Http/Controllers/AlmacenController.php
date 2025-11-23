<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        return response()->json(Almacen::with('sectores')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'capacidad' => 'required|numeric|min:0',
        ]);

        $almacen = Almacen::create($request->all());
        return response()->json($almacen->load('sectores'), 201);
    }

    public function show(Almacen $almacen)
    {
        return response()->json($almacen->load('sectores'));
    }

    public function update(Request $request, Almacen $almacen)
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'capacidad' => 'sometimes|required|numeric|min:0',
        ]);

        $almacen->update($request->all());
        return response()->json($almacen->load('sectores'));
    }

    public function destroy(Almacen $almacen)
    {
        $almacen->delete();
        return response()->json(null, 204);
    }
}
