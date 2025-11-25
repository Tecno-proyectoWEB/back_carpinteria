<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('servicios.ver')) {
            abort(403, 'No tiene permiso para ver servicios');
        }

        $query = Servicio::with('categoria')->where('activo', true);

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

        // Paginación
        $servicios = $query->paginate($request->get('per_page', 15));

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Servicios/Index', [
            'servicios' => $servicios,
            'categorias' => $categorias,
            'filters' => $request->only(['search', 'categoria_id', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
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
        if (!Auth::user()->tienePermiso('servicios.crear')) {
            abort(403, 'No tiene permiso para crear servicios');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categoria,id',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio_base.required' => 'El precio base es obligatorio.',
            'precio_base.numeric' => 'El precio debe ser un número.',
            'precio_base.min' => 'El precio no puede ser negativo.',
            'tiempo_estimado.integer' => 'El tiempo estimado debe ser un número entero.',
        ]);

        $validated['activo'] = $validated['activo'] ?? true;

        $servicio = Servicio::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Servicio creado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio creado exitosamente');
    }

    public function show(Servicio $servicio)
    {
        if (!Auth::user()->tienePermiso('servicios.ver')) {
            abort(403, 'No tiene permiso para ver servicios');
        }

        $servicio->load('categoria');

        return Inertia::render('Servicios/Show', [
            'servicio' => $servicio,
        ]);
    }

    public function edit(Servicio $servicio)
    {
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
        if (!Auth::user()->tienePermiso('servicios.editar')) {
            abort(403, 'No tiene permiso para editar servicios');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'nullable|exists:categoria,id',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'activo' => 'boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'precio_base.required' => 'El precio base es obligatorio.',
        ]);

        $datosAnteriores = $servicio->toArray();
        $servicio->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Servicio actualizado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio actualizado exitosamente');
    }

    public function destroy(Servicio $servicio)
    {
        if (!Auth::user()->tienePermiso('servicios.eliminar')) {
            abort(403, 'No tiene permiso para eliminar servicios');
        }

        // En lugar de eliminar, desactivar
        $servicio->update(['activo' => false]);

        \App\Models\Bitacora::create([
            'accion' => 'Servicio desactivado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('servicios.index')
            ->with('success', 'Servicio desactivado exitosamente');
    }
}

