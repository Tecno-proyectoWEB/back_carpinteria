<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function index()
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.ver')) {
            return response()->json(['message' => 'No tiene permiso para ver materiales'], 403);
        }

        return response()->json(Material::with(['sector', 'categoria'])->get());
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.crear')) {
            return response()->json(['message' => 'No tiene permiso para crear materiales'], 403);
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
            'sector_id' => 'required|exists:sector,id',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        $material = Material::create($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Material creado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_nuevos' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($material->load(['sector', 'categoria']), 201);
    }

    public function show(Material $material)
    {
        return response()->json($material->load(['sector', 'categoria']));
    }

    public function update(Request $request, Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.editar')) {
            return response()->json(['message' => 'No tiene permiso para editar materiales'], 403);
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
            'sector_id' => 'sometimes|required|exists:sector,id',
            'categoria_id' => 'sometimes|required|exists:categoria,id',
        ]);

        $datos_anteriores = $material->toArray();
        $material->update($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Material actualizado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($material->load(['sector', 'categoria']));
    }

    public function destroy(Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.eliminar')) {
            return response()->json(['message' => 'No tiene permiso para eliminar materiales'], 403);
        }

        // Registrar en bitácora antes de eliminar
        \App\Models\Bitacora::create([
            'accion' => 'Material eliminado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_anteriores' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $material->delete();
        return response()->json(null, 204);
    }
}
