<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return response()->json(Categoria::with('subcategoria')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ]);

        $categoria = Categoria::create($request->all());
        return response()->json($categoria->load('subcategoria'), 201);
    }

    public function show(Categoria $categoria)
    {
        return response()->json($categoria->load('subcategoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ]);

        $categoria->update($request->all());
        return response()->json($categoria->load('subcategoria'));
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return response()->json(null, 204);
    }
}
