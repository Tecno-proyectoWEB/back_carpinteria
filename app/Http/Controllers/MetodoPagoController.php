<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MetodoPagoController extends Controller
{
    public function index(Request $request)
    {
        $query = MetodoPago::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $metodosPago = $query->paginate($request->get('per_page', 15));

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('MetodosPago/Index', [
            'metodosPago' => $metodosPago,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/metodos-pago para crear'], 405);
        }

        return Inertia::render('MetodosPago/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago = MetodoPago::create($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($metodoPago, 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago creado exitosamente');
    }

    public function show(Request $request, MetodoPago $metodoPago)
    {
        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($metodoPago);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('MetodosPago/Show', [
            'metodoPago' => $metodoPago,
        ]);
    }

    public function edit(MetodoPago $metodoPago)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/metodos-pago/{id} para actualizar'], 405);
        }

        return Inertia::render('MetodosPago/Edit', [
            'metodoPago' => $metodoPago,
        ]);
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $metodoPago->update($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($metodoPago);
        }

        // Si es petición web, redirigir
        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago actualizado exitosamente');
    }

    public function destroy(Request $request, MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago eliminado exitosamente');
    }
}
