<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\Almacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SectorController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('sectores.ver')) {
            abort(403, 'No tiene permiso para ver sectores');
        }

        $query = Sector::with('almacen');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('tipo', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%")
                  ->orWhereHas('almacen', function($q) use ($search) {
                      $q->where('nombre', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Filtro por almacén
        if ($request->has('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $sectores = $query->paginate($request->get('per_page', 15));
        $almacenes = Almacen::orderBy('nombre')->get();

        return Inertia::render('Sectores/Index', [
            'sectores' => $sectores,
            'almacenes' => $almacenes,
            'filters' => $request->only(['search', 'almacen_id', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
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
            abort(403, 'No tiene permiso para crear sectores');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'required|exists:almacen,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'almacen_id.required' => 'El almacén es obligatorio.',
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'stock.numeric' => 'El stock debe ser un número.',
            'stock.min' => 'El stock no puede ser negativo.',
            'capacidad_maxima.numeric' => 'La capacidad máxima debe ser un número.',
            'capacidad_maxima.min' => 'La capacidad máxima no puede ser negativa.',
        ]);

        $sector = Sector::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Sector creado',
            'modulo' => 'Sector',
            'tabla_afectada' => 'sector',
            'registro_id' => $sector->id,
            'datos_nuevos' => $sector->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('sectores.index')
            ->with('success', 'Sector creado exitosamente');
    }

    public function show(Sector $sector)
    {
        if (!Auth::user()->tienePermiso('sectores.ver')) {
            abort(403, 'No tiene permiso para ver sectores');
        }

        $sector->load(['almacen', 'materiales']);

        return Inertia::render('Sectores/Show', [
            'sector' => $sector,
        ]);
    }

    public function edit(Sector $sector)
    {
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
            abort(403, 'No tiene permiso para editar sectores');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'capacidad_maxima' => 'nullable|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'almacen_id' => 'required|exists:almacen,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'almacen_id.required' => 'El almacén es obligatorio.',
            'almacen_id.exists' => 'El almacén seleccionado no existe.',
            'stock.numeric' => 'El stock debe ser un número.',
            'stock.min' => 'El stock no puede ser negativo.',
            'capacidad_maxima.numeric' => 'La capacidad máxima debe ser un número.',
            'capacidad_maxima.min' => 'La capacidad máxima no puede ser negativa.',
        ]);

        $datosAnteriores = $sector->toArray();
        $sector->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Sector actualizado',
            'modulo' => 'Sector',
            'tabla_afectada' => 'sector',
            'registro_id' => $sector->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $sector->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('sectores.index')
            ->with('success', 'Sector actualizado exitosamente');
    }

    public function destroy(Sector $sector)
    {
        if (!Auth::user()->tienePermiso('sectores.eliminar')) {
            abort(403, 'No tiene permiso para eliminar sectores');
        }

        \App\Models\Bitacora::create([
            'accion' => 'Sector eliminado',
            'modulo' => 'Sector',
            'tabla_afectada' => 'sector',
            'registro_id' => $sector->id,
            'datos_anteriores' => $sector->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $sector->delete();

        return redirect()->route('sectores.index')
            ->with('success', 'Sector eliminado exitosamente');
    }
}

