<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Categoria;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $query = Servicio::with('categoria');

        // Filtro por activo (solo para API, web muestra todos)
        if ($request->wantsJson() || $request->is('api/*')) {
            $query->where('activo', true);
        }

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
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
        $servicios = $query->paginate($request->get('per_page', 15));
        $categorias = Categoria::where('activo', true)->get();

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Servicios/Index', [
            'servicios' => $servicios,
            'categorias' => $categorias,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'categoria_id', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/servicios para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('servicios.crear')) {
            abort(403, 'No tiene permiso para crear servicios');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Servicios/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear servicios'], 403);
            }
            abort(403, 'No tiene permiso para crear servicios');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'categoria_id' => 'nullable|exists:categoria,id',
            'activo' => 'boolean',
        ]);

        $servicio = Servicio::create($validated);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio creado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($servicio->load('categoria'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado exitosamente');
    }

    public function show(Request $request, Servicio $servicio)
    {
        $servicio->load('categoria');

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($servicio);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Servicios/Show', [
            'servicio' => $servicio,
        ]);
    }

    public function edit(Servicio $servicio)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/servicios/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('servicios.editar')) {
            abort(403, 'No tiene permiso para editar servicios');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Servicios/Edit', [
            'servicio' => $servicio->load('categoria'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Servicio $servicio)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar servicios'], 403);
            }
            abort(403, 'No tiene permiso para editar servicios');
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'sometimes|required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'categoria_id' => 'nullable|exists:categoria,id',
            'activo' => 'boolean',
        ]);

        $datos_anteriores = $servicio->toArray();
        $servicio->update($validated);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio actualizado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($servicio->load('categoria'));
        }

        // Si es petición web, redirigir
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado exitosamente');
    }

    public function destroy(Request $request, Servicio $servicio)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar servicios'], 403);
            }
            abort(403, 'No tiene permiso para eliminar servicios');
        }

        $datos_anteriores = $servicio->toArray();
        $servicio->update(['activo' => false]);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio desactivado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Servicio desactivado'], 200);
        }

        // Si es petición web, redirigir
        return redirect()->route('servicios.index')
            ->with('success', 'Servicio desactivado exitosamente');
    }
}
