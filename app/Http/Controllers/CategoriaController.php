<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Subcategoria;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::with('subcategoria');

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('activo')) {
            $query->where('activo', $request->activo === 'activo');
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
        $categorias = $query->paginate($request->get('per_page', 15));
        $subcategorias = Subcategoria::orderBy('nombre')->get();

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/categorias para crear'], 405);
        }

        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return Inertia::render('Categorias/Create', [
            'subcategorias' => $subcategorias,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ]);

        $categoria = Categoria::create($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($categoria->load('subcategoria'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function show(Request $request, Categoria $categoria)
    {
        $categoria->load('subcategoria');

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($categoria);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Categorias/Show', [
            'categoria' => $categoria,
        ]);
    }

    public function edit(Categoria $categoria)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use PUT /api/categorias/{id} para actualizar'], 405);
        }

        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria->load('subcategoria'),
            'subcategorias' => $subcategorias,
        ]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ]);

        $categoria->update($validated);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($categoria->load('subcategoria'));
        }

        // Si es petición web, redirigir
        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        $categoria->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente');
    }
}
