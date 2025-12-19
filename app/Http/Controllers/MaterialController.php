<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Categoria;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MaterialController extends BaseController
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver materiales'], 403);
            }
            abort(403, 'No tiene permiso para ver materiales');
        }

        $query = Material::with('categoria');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // Filtro por stock bajo
        if ($request->filled('stock_bajo') && $request->stock_bajo) {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $materiales = $query->paginate($request->get('per_page', 15));
        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'categorias' => $categorias,
            'filters' => $request->only(['search', 'categoria_id', 'stock_bajo', 'sort_by', 'sort_dir', 'per_page']),
        ]);
    }

    public function create(Request $request)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/materiales para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('materiales.crear')) {
            abort(403, 'No tiene permiso para crear materiales');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Materiales/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear materiales'], 403);
            }
            abort(403, 'No tiene permiso para crear materiales');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        $material = Material::create($validated);

        // Registrar en bitácora
        Bitacora::create([
            'tipo_accion_id' => 1, // CREAR
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => null,
            'datos_nuevos' => $material->toArray(),
            'descripcion' => "Material creado: {$material->nombre}",
            'ip' => $request->ip(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material->load('categoria'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('materiales.index')
            ->with('success', 'Material creado exitosamente');
    }

    public function show(Request $request, Material $material)
    {
        $material->load(['categoria', 'sector']);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Materiales/Show', [
            'material' => $material,
        ]);
    }

    public function edit(Request $request, Material $material)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/materiales/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('materiales.editar')) {
            abort(403, 'No tiene permiso para editar materiales');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Materiales/Edit', [
            'material' => $material->load('categoria'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar materiales'], 403);
            }
            abort(403, 'No tiene permiso para editar materiales');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'punto_reorden' => 'nullable|integer|min:0',
            'precio' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:255',
            'activo' => 'nullable|boolean',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        // Manejar el campo activo explícitamente
        if ($request->has('activo')) {
            $activoInput = $request->activo;
            if (is_bool($activoInput)) {
                $validated['activo'] = $activoInput;
            } elseif (is_string($activoInput)) {
                $validated['activo'] = in_array(strtolower($activoInput), ['true', '1', 't', 'yes', 'on']);
            } elseif (is_int($activoInput) || is_float($activoInput)) {
                $validated['activo'] = (bool)$activoInput;
            } else {
                $validated['activo'] = (bool)$activoInput;
            }
        } else {
            $validated['activo'] = (bool)$material->activo;
        }

        $datosAnteriores = $material->toArray();
        $material->update($validated);

        // Registrar en bitácora
        Bitacora::create([
            'tipo_accion_id' => 2, // ACTUALIZAR
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $material->fresh()->toArray(),
            'descripcion' => "Material actualizado: {$material->nombre}",
            'ip' => $request->ip(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material->load('categoria'));
        }

        // Si es petición web, redirigir
        return redirect()->route('materiales.index')
            ->with('success', 'Material actualizado exitosamente');
    }

    public function destroy(Request $request, Material $material)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('materiales.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar materiales'], 403);
            }
            abort(403, 'No tiene permiso para eliminar materiales');
        }

        // Registrar en bitácora antes de eliminar
        Bitacora::create([
            'tipo_accion_id' => 3, // ELIMINAR
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => $material->toArray(),
            'datos_nuevos' => null,
            'descripcion' => "Material eliminado: {$material->nombre}",
            'ip' => $request->ip(),
        ]);

        // Soft delete
        $material->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('materiales.index')
            ->with('success', 'Material eliminado exitosamente');
    }
}
