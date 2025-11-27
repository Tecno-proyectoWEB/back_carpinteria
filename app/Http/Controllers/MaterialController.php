<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index()
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.ver')) {
            return back()->withErrors(['error' => 'No tiene permiso para ver materiales']);
        }

        return Inertia::render('Materiales/Index', [
            'materiales' => Material::with('categoria')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Materiales/Create', [
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear materiales']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'categoria_id' => 'nullable|exists:categoria,id',
        ]);

        $material = Material::create($request->all());

        return redirect()->route('materiales.index')->with('success', 'Material creado exitosamente');
    }

    public function show(Material $material)
    {
        return Inertia::render('Materiales/Show', [
            'material' => $material->load('categoria'),
        ]);
    }

    public function edit(Material $material)
    {
        return Inertia::render('Materiales/Edit', [
            'material' => $material->load('categoria'),
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar materiales']);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'categoria_id' => 'nullable|exists:categoria,id',
        ]);

        $material->update($request->all());

        return redirect()->route('materiales.index')->with('success', 'Material actualizado exitosamente');
    }

    public function destroy(Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar materiales']);
        }

        $material->delete();
        return redirect()->route('materiales.index')->with('success', 'Material eliminado exitosamente');
    }
}
