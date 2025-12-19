<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use App\Models\Almacen;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SectorController extends BaseController
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('sectores.ver')) {
            return $this->respondError('No tiene permiso para ver sectores', 403);
        }

        $query = Sector::with('almacen');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('tipo', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        if ($this->isApiRequest($request)) {
            return response()->json($query->get());
        }

        $sectores = $query->paginate($request->get('per_page', 15));
        $almacenes = Almacen::orderBy('nombre')->get();

        return Inertia::render('Sectores/Index', [
            'sectores' => $sectores,
            'almacenes' => $almacenes,
            'filters' => $request->only(['search', 'almacen_id', 'sort_by', 'sort_dir', 'per_page']),
        ]);
    }

    public function create(Request $request)
    {
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => 'Use POST /api/sectores para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('sectores.crear')) {
            abort(403, 'No tiene permiso para crear sectores');
        }

        $almacenes = Almacen::orderBy('nombre')->get();

        return Inertia::render('Sectores/Create', [
            'almacenes' => $almacenes,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('sectores.crear')) {
            return $this->respondError('No tiene permiso para crear sectores', 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'required|exists:almacen,id',
            'activo' => 'boolean',
        ]);

        $sector = Sector::create($validated);

        if ($this->isApiRequest($request)) {
            return response()->json($sector->load('almacen'), 201);
        }

        return redirect()->route('sectores.index')
            ->with('success', 'Sector creado exitosamente');
    }

    public function show(Request $request, Sector $sector)
    {
        if (!Auth::user()->tienePermiso('sectores.ver')) {
            return $this->respondError('No tiene permiso para ver sectores', 403);
        }

        $sector->load(['almacen', 'materiales' => function ($q) {
            $q->select('id', 'nombre', 'stock_actual', 'sector_id');
        }]);

        if ($this->isApiRequest($request)) {
            return response()->json($sector);
        }

        return Inertia::render('Sectores/Show', [
            'sector' => $sector,
        ]);
    }

    public function edit(Request $request, Sector $sector)
    {
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => 'Use PUT /api/sectores/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('sectores.editar')) {
            abort(403, 'No tiene permiso para editar sectores');
        }

        $almacenes = Almacen::orderBy('nombre')->get();

        return Inertia::render('Sectores/Edit', [
            'sector' => $sector->load('almacen'),
            'almacenes' => $almacenes,
        ]);
    }

    public function update(Request $request, Sector $sector)
    {
        if (!Auth::user()->tienePermiso('sectores.editar')) {
            return $this->respondError('No tiene permiso para editar sectores', 403);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'sometimes|required|exists:almacen,id',
            'activo' => 'boolean',
        ]);

        $sector->update($validated);

        if ($this->isApiRequest($request)) {
            return response()->json($sector->load('almacen'));
        }

        return redirect()->route('sectores.index')
            ->with('success', 'Sector actualizado exitosamente');
    }

    public function destroy(Request $request, Sector $sector)
    {
        if (!Auth::user()->tienePermiso('sectores.eliminar')) {
            return $this->respondError('No tiene permiso para eliminar sectores', 403);
        }

        $sector->delete();

        if ($this->isApiRequest($request)) {
            return response()->json(null, 204);
        }

        return redirect()->route('sectores.index')
            ->with('success', 'Sector eliminado exitosamente');
    }
}
