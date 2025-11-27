<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\Almacen;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        $query = Sector::with('almacen');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
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
        $sectores = $query->paginate($request->get('per_page', 15));
        $almacenes = Almacen::all();

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Sectores/Index', [
            'sectores' => $sectores,
            'almacenes' => $almacenes,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/sectores para crear'], 405);
        }

        $almacenes = Almacen::all();

        return Inertia::render('Sectores/Create', [
            'almacenes' => $almacenes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'required|exists:almacen,id',
        ]);

        $sector = Sector::create($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($sector->load('almacen'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('sectores.index')
            ->with('success', 'Sector creado exitosamente');
    }

    public function show(Request $request, Sector $sector)
    {
        $sector->load('almacen');

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($sector);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Sectores/Show', [
            'sector' => $sector,
        ]);
    }

    public function edit(Sector $sector)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/sectores/{id} para actualizar'], 405);
        }

        $almacenes = Almacen::all();

        return Inertia::render('Sectores/Edit', [
            'sector' => $sector->load('almacen'),
            'almacenes' => $almacenes,
        ]);
    }

    public function update(Request $request, Sector $sector)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'sometimes|required|exists:almacen,id',
        ]);

        $sector->update($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($sector->load('almacen'));
        }

        // Si es petición web, redirigir
        return redirect()->route('sectores.index')
            ->with('success', 'Sector actualizado exitosamente');
    }

    public function destroy(Request $request, Sector $sector)
    {
        $sector->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('sectores.index')
            ->with('success', 'Sector eliminado exitosamente');
    }
}
