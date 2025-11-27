<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    use HasPermissions;

    public function index(Request $request)
    {
        $this->autorizarPermiso('productos.ver', 'No tiene permiso para ver productos');

        $query = Producto::with('categoria');

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
        $productos = $query->paginate($request->get('per_page', 15));

        // Obtener categorías para filtro
        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'filters' => $request->only(['search', 'categoria_id', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        $this->autorizarPermiso('productos.crear', 'No tiene permiso para crear productos');

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarPermiso('productos.crear', 'No tiene permiso para crear productos');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio debe ser un número.',
            'precio_unitario.min' => 'El precio no puede ser negativo.',
        ]);

        // Manejar imagen si se sube
        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $producto = Producto::create($validated);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Producto creado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_nuevos' => $producto->toArray(),
            'usuario_id' => auth()->user()->id,
            'fecha' => now(),
        ]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Producto $producto)
    {
        $this->autorizarPermiso('productos.ver', 'No tiene permiso para ver productos');

        $producto->load('categoria');

        return Inertia::render('Productos/Show', [
            'producto' => $producto,
        ]);
    }

    public function edit(Producto $producto)
    {
        $this->autorizarPermiso('productos.editar', 'No tiene permiso para editar productos');

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Edit', [
            'producto' => $producto->load('categoria'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $this->autorizarPermiso('productos.editar', 'No tiene permiso para editar productos');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categoria,id',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'stock.required' => 'El stock es obligatorio.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
        ]);

        // Manejar imagen si se sube
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen) {
                \Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $datosAnteriores = $producto->toArray();
        $producto->update($validated);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Producto actualizado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $producto->toArray(),
            'usuario_id' => auth()->user()->id,
            'fecha' => now(),
        ]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Producto $producto)
    {
        $this->autorizarPermiso('productos.eliminar', 'No tiene permiso para eliminar productos');

        // Eliminar imagen si existe
        if ($producto->imagen) {
            \Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Producto eliminado',
            'modulo' => 'Producto',
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'datos_anteriores' => $producto->toArray(),
            'usuario_id' => auth()->user()->id,
            'fecha' => now(),
        ]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }
}

