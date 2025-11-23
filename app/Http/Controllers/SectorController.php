<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Http\Request;

class SectorController extends Controller
{
    public function index()
    {
        return response()->json(Sector::with('almacen')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'required|exists:almacen,id',
        ]);

        $sector = Sector::create($request->all());
        return response()->json($sector->load('almacen'), 201);
    }

    public function show(Sector $sector)
    {
        return response()->json($sector->load('almacen'));
    }

    public function update(Request $request, Sector $sector)
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'sometimes|required|exists:almacen,id',
        ]);

        $sector->update($request->all());
        return response()->json($sector->load('almacen'));
    }

    public function destroy(Sector $sector)
    {
        $sector->delete();
        return response()->json(null, 204);
    }
}
