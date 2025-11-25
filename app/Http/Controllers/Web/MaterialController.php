<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Categoria;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('materiales.ver')) {
            abort(403, 'No tiene permiso para ver materiales');
        }

        $query = Material::with(['categoria', 'sector']);

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Filtros
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('sector_id')) {
            $query->where('sector_id', $request->sector_id);
        }

        if ($request->has('stock_bajo')) {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $materiales = $query->paginate($request->get('per_page', 15));

        $categorias = Categoria::where('activo', true)->get();
        $sectores = Sector::with('almacen')->get();

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'categorias' => $categorias,
            'sectores' => $sectores,
            'filters' => $request->only(['search', 'categoria_id', 'sector_id', 'stock_bajo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('materiales.crear')) {
            abort(403, 'No tiene permiso para crear materiales');
        }

        $categorias = Categoria::where('activo', true)->get();
        $sectores = Sector::with('almacen')->get();

        return Inertia::render('Materiales/Create', [
            'categorias' => $categorias,
            'sectores' => $sectores,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('materiales.crear')) {
            abort(403, 'No tiene permiso para crear materiales');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'sector_id' => 'required|exists:sector,id',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'sector_id.required' => 'El sector es obligatorio.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_actual.integer' => 'El stock debe ser un número entero.',
            'stock_actual.min' => 'El stock no puede ser negativo.',
        ]);

        // Manejar imagen
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('materiales', 'public');
        }

        $validated['activo'] = $validated['activo'] ?? true;

        $material = Material::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Material creado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_nuevos' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('materiales.index')
            ->with('success', 'Material creado exitosamente');
    }

    public function show(Material $material)
    {
        if (!Auth::user()->tienePermiso('materiales.ver')) {
            abort(403, 'No tiene permiso para ver materiales');
        }

        $material->load(['categoria', 'sector.almacen']);

        return Inertia::render('Materiales/Show', [
            'material' => $material,
        ]);
    }

    public function edit(Material $material)
    {
        if (!Auth::user()->tienePermiso('materiales.editar')) {
            abort(403, 'No tiene permiso para editar materiales');
        }

        $categorias = Categoria::where('activo', true)->get();
        $sectores = Sector::with('almacen')->get();

        return Inertia::render('Materiales/Edit', [
            'material' => $material->load(['categoria', 'sector']),
            'categorias' => $categorias,
            'sectores' => $sectores,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        if (!Auth::user()->tienePermiso('materiales.editar')) {
            abort(403, 'No tiene permiso para editar materiales');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'sector_id' => 'required|exists:sector,id',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|max:2048',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'sector_id.required' => 'El sector es obligatorio.',
        ]);

        // Manejar imagen
        if ($request->hasFile('imagen')) {
            if ($material->imagen) {
                \Storage::disk('public')->delete($material->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('materiales', 'public');
        }

        $datosAnteriores = $material->toArray();
        $material->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Material actualizado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('materiales.index')
            ->with('success', 'Material actualizado exitosamente');
    }

    public function destroy(Material $material)
    {
        if (!Auth::user()->tienePermiso('materiales.eliminar')) {
            abort(403, 'No tiene permiso para eliminar materiales');
        }

        if ($material->imagen) {
            \Storage::disk('public')->delete($material->imagen);
        }

        $material->delete();

        \App\Models\Bitacora::create([
            'accion' => 'Material eliminado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_anteriores' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('materiales.index')
            ->with('success', 'Material eliminado exitosamente');
    }
}
