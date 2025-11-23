<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function index()
    {
        return response()->json(Permiso::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $permiso = Permiso::create($request->all());
        return response()->json($permiso, 201);
    }

    public function show(Permiso $permiso)
    {
        return response()->json($permiso);
    }

    public function update(Request $request, Permiso $permiso)
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
        ]);

        $permiso->update($request->all());
        return response()->json($permiso);
    }

    public function destroy(Permiso $permiso)
    {
        $permiso->delete();
        return response()->json(null, 204);
    }
}
