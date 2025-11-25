<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Subcategoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('categorias.ver')) {
            abort(403, 'No tiene permiso para ver categorías');
        }

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

        $categorias = $query->paginate($request->get('per_page', 15));
        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return Inertia::render('Categorias/Index', [
            'categorias' => $categorias,
            'subcategorias' => $subcategorias,
            'filters' => $request->only(['search', 'activo', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('categorias.crear')) {
            abort(403, 'No tiene permiso para crear categorías');
        }

        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return Inertia::render('Categorias/Create', [
            'subcategorias' => $subcategorias,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('categorias.crear')) {
            abort(403, 'No tiene permiso para crear categorías');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'subcategoria_id.exists' => 'La subcategoría seleccionada no existe.',
        ]);

        $categoria = Categoria::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Categoría creada',
            'modulo' => 'Categoría',
            'tabla_afectada' => 'categoria',
            'registro_id' => $categoria->id,
            'datos_nuevos' => $categoria->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada exitosamente');
    }

    public function show(Categoria $categoria)
    {
        if (!Auth::user()->tienePermiso('categorias.ver')) {
            abort(403, 'No tiene permiso para ver categorías');
        }

        $categoria->load(['subcategoria', 'productos', 'servicios', 'materiales']);

        return Inertia::render('Categorias/Show', [
            'categoria' => $categoria,
        ]);
    }

    public function edit(Categoria $categoria)
    {
        if (!Auth::user()->tienePermiso('categorias.editar')) {
            abort(403, 'No tiene permiso para editar categorías');
        }

        $subcategorias = Subcategoria::orderBy('nombre')->get();

        return Inertia::render('Categorias/Edit', [
            'categoria' => $categoria->load('subcategoria'),
            'subcategorias' => $subcategorias,
        ]);
    }

    public function update(Request $request, Categoria $categoria)
    {
        if (!Auth::user()->tienePermiso('categorias.editar')) {
            abort(403, 'No tiene permiso para editar categorías');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
            'subcategoria_id' => 'nullable|exists:subcategoria,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'subcategoria_id.exists' => 'La subcategoría seleccionada no existe.',
        ]);

        $datosAnteriores = $categoria->toArray();
        $categoria->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Categoría actualizada',
            'modulo' => 'Categoría',
            'tabla_afectada' => 'categoria',
            'registro_id' => $categoria->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $categoria->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada exitosamente');
    }

    public function destroy(Categoria $categoria)
    {
        if (!Auth::user()->tienePermiso('categorias.eliminar')) {
            abort(403, 'No tiene permiso para eliminar categorías');
        }

        \App\Models\Bitacora::create([
            'accion' => 'Categoría eliminada',
            'modulo' => 'Categoría',
            'tabla_afectada' => 'categoria',
            'registro_id' => $categoria->id,
            'datos_anteriores' => $categoria->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $categoria->delete();

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría eliminada exitosamente');
    }
}

