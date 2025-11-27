<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Categoria;
use App\Models\Sector;
use App\Http\Controllers\MenuController;
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

        $query = Material::with(['categoria', 'sector']);

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

        // Filtro por sector
        if ($request->filled('sector_id')) {
            $query->where('sector_id', $request->sector_id);
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
        $sectores = Sector::where('activo', true)->get();

        // Compartir usuario autenticado
        $this->shareAuthUser($request);

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'categorias' => $categorias,
            'sectores' => $sectores,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'categoria_id', 'sector_id', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/materiales para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('materiales.crear')) {
            abort(403, 'No tiene permiso para crear materiales');
        }

        $categorias = Categoria::where('activo', true)->get();
        $sectores = Sector::where('activo', true)->get();

        // Compartir usuario autenticado
        $this->shareAuthUser(request());

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser(request()->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador(request()->path());

        return Inertia::render('Materiales/Create', [
            'categorias' => $categorias,
            'sectores' => $sectores,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
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
            'sector_id' => 'required|exists:sector,id',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        $material = Material::create($validated);

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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material->load(['sector', 'categoria']), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('materiales.index')
            ->with('success', 'Material creado exitosamente');
    }

    public function show(Request $request, Material $material)
    {
        $material->load(['sector', 'categoria']);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material);
        }

        // Compartir usuario autenticado
        $this->shareAuthUser($request);

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        // Si es petición web, retornar Inertia
        return Inertia::render('Materiales/Show', [
            'material' => $material,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function edit(Material $material)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/materiales/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('materiales.editar')) {
            abort(403, 'No tiene permiso para editar materiales');
        }

        $categorias = Categoria::where('activo', true)->get();
        $sectores = Sector::where('activo', true)->get();

        // Compartir usuario autenticado
        $this->shareAuthUser(request());

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser(request()->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador(request()->path());

        return Inertia::render('Materiales/Edit', [
            'material' => $material->load(['sector', 'categoria']),
            'categorias' => $categorias,
            'sectores' => $sectores,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
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
            'activo' => 'boolean',
            'sector_id' => 'required|exists:sector,id',
            'categoria_id' => 'required|exists:categoria,id',
        ]);

        $datos_anteriores = $material->toArray();
        $material->update($validated);

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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($material->load(['sector', 'categoria']));
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
        \App\Models\Bitacora::create([
            'accion' => 'Material eliminado',
            'modulo' => 'Material',
            'tabla_afectada' => 'material',
            'registro_id' => $material->id,
            'datos_anteriores' => $material->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
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
