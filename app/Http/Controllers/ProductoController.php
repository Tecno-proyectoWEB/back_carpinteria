<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver productos'], 403);
            }
            abort(403, 'No tiene permiso para ver productos');
        }

        $query = Producto::with('categoria');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'ILIKE', "%{$search}%")
                  ->orWhere('descripcion', 'ILIKE', "%{$search}%");
            });
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
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
        $productos = $query->paginate($request->get('per_page', 15));
        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'filters' => $request->only(['search', 'categoria_id', 'sort_by', 'sort_dir', 'per_page']),
        ]);
    }

    public function create(Request $request)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/productos para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('productos.crear')) {
            abort(403, 'No tiene permiso para crear productos');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Create', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear productos'], 403);
            }
            abort(403, 'No tiene permiso para crear productos');
        }

        // Log para debugging
        Log::info('Creando producto', [
            'request_data' => $request->except(['imagen']),
            'user_id' => Auth::id(),
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|integer|exists:categoria,id',
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
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        try {
            $producto = Producto::create($validated);
            Log::info('Producto creado exitosamente', ['producto_id' => $producto->id]);
        } catch (\Exception $e) {
            Log::error('Error al crear producto', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // Registrar en bitácora
        Bitacora::create([
            'tipo_accion_id' => 1, // CREAR
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => null,
            'datos_nuevos' => $producto->toArray(),
            'descripcion' => "Producto creado: {$producto->nombre}",
            'ip' => $request->ip(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($producto->load('categoria'), 201);
        }

        // Si es petición web, redirigir
        return redirect()->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function show(Request $request, Producto $producto)
    {
        if (!Auth::user()->tienePermiso('productos.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver productos'], 403);
            }
            abort(403, 'No tiene permiso para ver productos');
        }

        $producto->load('categoria');

        // Serialización explícita para asegurar presencia de campos numéricos
        $data = [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'categoria' => $producto->categoria ? [
                'id' => $producto->categoria->id,
                'nombre' => $producto->categoria->nombre,
            ] : null,
            // Asegurar tipos correctos
            'stock' => (int) ($producto->stock ?? 0),
            'stock_minimo' => (int) ($producto->stock_minimo ?? 0),
            'precio_unitario' => (float) ($producto->precio_unitario ?? 0),
            'imagen' => $producto->imagen,
        ];

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($data);
        }

        // Log de verificación
        \Illuminate\Support\Facades\Log::info('Mostrar producto', [
            'producto_id' => $producto->id,
            'stock' => $producto->stock,
            'stock_minimo' => $producto->stock_minimo,
            'precio_unitario' => $producto->precio_unitario,
        ]);

        // Si es petición web, retornar Inertia
        return Inertia::render('Productos/Show', [
            'producto' => $data,
        ]);
    }

    public function edit(Request $request, Producto $producto)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use PATCH /api/productos/{id} para actualizar'], 405);
        }

        if (!Auth::user()->tienePermiso('productos.editar')) {
            abort(403, 'No tiene permiso para editar productos');
        }

        $categorias = Categoria::where('activo', true)->get();

        return Inertia::render('Productos/Edit', [
            'producto' => $producto->load('categoria'),
            'categorias' => $categorias,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar productos'], 403);
            }
            abort(403, 'No tiene permiso para editar productos');
        }

        // Log para debugging
        Log::info('Actualizando producto', [
            'producto_id' => $producto->id,
            'request_data' => $request->except(['imagen']),
            'user_id' => Auth::id(),
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|integer|exists:categoria,id',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|max:2048',
        ]);

        // Manejar imagen si se sube
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($producto->imagen) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        }

        $datosAnteriores = $producto->toArray();

        try {
            $producto->update($validated);
            Log::info('Producto actualizado exitosamente', ['producto_id' => $producto->id]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar producto', [
                'producto_id' => $producto->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // Registrar en bitácora
        Bitacora::create([
            'tipo_accion_id' => 2, // ACTUALIZAR
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $producto->fresh()->toArray(),
            'descripcion' => "Producto actualizado: {$producto->nombre}",
            'ip' => $request->ip(),
        ]);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($producto->load('categoria'));
        }

        // Si es petición web, redirigir
        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
    }

    public function destroy(Request $request, Producto $producto)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('productos.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar productos'], 403);
            }
            abort(403, 'No tiene permiso para eliminar productos');
        }

        // Registrar en bitácora antes de eliminar (soft delete)
        Bitacora::create([
            'tipo_accion_id' => 3, // ELIMINAR
            'tabla_afectada' => 'producto',
            'registro_id' => $producto->id,
            'usuario_id' => Auth::id(),
            'datos_anteriores' => $producto->toArray(),
            'datos_nuevos' => null,
            'descripcion' => "Producto eliminado: {$producto->nombre}",
            'ip' => $request->ip(),
        ]);

        // Soft delete - no elimina físicamente el registro
        $producto->delete();

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado exitosamente');
    }
}
