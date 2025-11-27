<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('ruc', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('activo')) {
            $query->where('activo', $request->activo === 'activo');
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
        $proveedores = $query->paginate($request->get('per_page', 15));

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/proveedores para crear'], 405);
        }

        return Inertia::render('Proveedores/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'ruc' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $proveedor = Proveedor::create($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($proveedor, 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado exitosamente');
    }

    public function show(Request $request, Proveedor $proveedor)
    {
        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($proveedor);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/proveedores/{id} para actualizar'], 405);
        }

        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor,
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'nombre' => 'nullable|string|max:255',
            'ruc' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'persona_contacto' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $proveedor->update($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($proveedor);
        }

        // Si es petición web, redirigir
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado exitosamente');
    }

    public function destroy(Request $request, Proveedor $proveedor)
    {
        $proveedor->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado exitosamente');
    }
}
