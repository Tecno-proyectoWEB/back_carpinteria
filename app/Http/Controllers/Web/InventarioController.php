<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\MovimientoInventario;
use App\Models\Material;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InventarioController extends Controller
{
    use HasPermissions;
    public function index(Request $request)
    {
        $this->autorizarPermiso('inventario.ver', 'No tiene permiso para ver inventario');

        $query = MovimientoInventario::with(['material', 'producto', 'usuario', 'compra', 'pedido']);

        // Filtros
        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('material_id') && $request->material_id) {
            $query->where('material_id', $request->material_id);
        }

        if ($request->has('producto_id') && $request->producto_id) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('material', function($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%");
                })
                ->orWhereHas('producto', function($q) use ($search) {
                    $q->where('nombre', 'ILIKE', "%{$search}%");
                })
                ->orWhere('motivo', 'ILIKE', "%{$search}%")
                ->orWhere('observaciones', 'ILIKE', "%{$search}%");
            });
        }

        $movimientos = $query->orderBy('fecha', 'desc')->paginate($request->get('per_page', 20));
        $materiales = Material::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

        return Inertia::render('Inventario/Index', [
            'movimientos' => $movimientos,
            'materiales' => $materiales,
            'productos' => $productos,
            'filters' => $request->only(['tipo', 'material_id', 'producto_id', 'fecha_desde', 'fecha_hasta', 'search']),
        ]);
    }

    public function stock(Request $request)
    {
        $this->autorizarPermiso('inventario.ver', 'No tiene permiso para ver inventario');

        $queryMateriales = Material::with('categoria', 'sector');
        $queryProductos = Producto::with('categoria');

        // Filtros para materiales
        if ($request->has('material_search') && $request->material_search) {
            $search = $request->material_search;
            $queryMateriales->where('nombre', 'ILIKE', "%{$search}%");
        }

        if ($request->has('material_bajo_stock') && $request->material_bajo_stock) {
            $queryMateriales->whereRaw('stock_actual <= stock_minimo');
        }

        // Filtros para productos
        if ($request->has('producto_search') && $request->producto_search) {
            $search = $request->producto_search;
            $queryProductos->where('nombre', 'ILIKE', "%{$search}%");
        }

        if ($request->has('producto_bajo_stock') && $request->producto_bajo_stock) {
            $queryProductos->whereRaw('stock <= stock_minimo');
        }

        $materiales = $queryMateriales->orderBy('nombre')->get();
        $productos = $queryProductos->orderBy('nombre')->get();

        // Estadísticas
        $totalMateriales = Material::count();
        $materialesBajoStock = Material::whereRaw('stock_actual <= stock_minimo')->count();
        $totalProductos = Producto::count();
        $productosBajoStock = Producto::whereRaw('stock <= stock_minimo')->count();

        return Inertia::render('Inventario/Stock', [
            'materiales' => $materiales,
            'productos' => $productos,
            'estadisticas' => [
                'total_materiales' => $totalMateriales,
                'materiales_bajo_stock' => $materialesBajoStock,
                'total_productos' => $totalProductos,
                'productos_bajo_stock' => $productosBajoStock,
            ],
            'filters' => $request->only(['material_search', 'material_bajo_stock', 'producto_search', 'producto_bajo_stock']),
        ]);
    }

    public function create()
    {
        if (!$this->tieneAlgunPermiso(['inventario.ingreso', 'inventario.salida'])) {
            abort(403, 'No tiene permiso para crear movimientos de inventario');
        }

        $materiales = Material::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get();

        return Inertia::render('Inventario/Create', [
            'materiales' => $materiales,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos según tipo
        if ($request->tipo === 'INGRESO' && !$this->tienePermiso('inventario.ingreso')) {
            abort(403, 'No tiene permiso para registrar ingresos');
        }

        if ($request->tipo === 'SALIDA' && !$this->tienePermiso('inventario.salida')) {
            abort(403, 'No tiene permiso para registrar salidas');
        }

        $validated = $request->validate([
            'tipo' => 'required|in:INGRESO,SALIDA',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'material_id' => 'required_without:producto_id|exists:material,id',
            'producto_id' => 'required_without:material_id|exists:producto,id',
        ], [
            'tipo.required' => 'El tipo de movimiento es obligatorio.',
            'tipo.in' => 'El tipo debe ser INGRESO o SALIDA.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'material_id.required_without' => 'Debe seleccionar un material o un producto.',
            'producto_id.required_without' => 'Debe seleccionar un material o un producto.',
        ]);

        DB::beginTransaction();
        try {
            $movimiento = MovimientoInventario::create([
                'tipo' => $validated['tipo'],
                'cantidad' => $validated['cantidad'],
                'motivo' => $validated['motivo'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'material_id' => $validated['material_id'] ?? null,
                'producto_id' => $validated['producto_id'] ?? null,
                'compra_id' => null,
                'pedido_id' => null,
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);

            // Actualizar stock
            if (isset($validated['material_id'])) {
                $material = Material::findOrFail($validated['material_id']);
                if ($validated['tipo'] === 'INGRESO') {
                    $material->stock_actual += $validated['cantidad'];
                } else {
                    if ($material->stock_actual < $validated['cantidad']) {
                        DB::rollBack();
                        return back()->withErrors(['cantidad' => 'Stock insuficiente. Stock actual: ' . $material->stock_actual]);
                    }
                    $material->stock_actual -= $validated['cantidad'];
                }
                $material->save();
            } elseif (isset($validated['producto_id'])) {
                $producto = Producto::findOrFail($validated['producto_id']);
                if ($validated['tipo'] === 'INGRESO') {
                    $producto->stock += $validated['cantidad'];
                } else {
                    if ($producto->stock < $validated['cantidad']) {
                        DB::rollBack();
                        return back()->withErrors(['cantidad' => 'Stock insuficiente. Stock actual: ' . $producto->stock]);
                    }
                    $producto->stock -= $validated['cantidad'];
                }
                $producto->save();
            }

            DB::commit();

            // Registrar en bitácora
            \App\Models\Bitacora::create([
                'accion' => 'Movimiento de inventario creado',
                'modulo' => 'Inventario',
                'tabla_afectada' => 'movimiento_inventario',
                'registro_id' => $movimiento->id,
                'datos_nuevos' => $movimiento->toArray(),
                'usuario_id' => auth()->id(),
                'fecha' => now(),
            ]);

            return redirect()->route('inventario.index')
                ->with('success', 'Movimiento de inventario registrado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el movimiento: ' . $e->getMessage()]);
        }
    }

    public function show(MovimientoInventario $movimientoInventario)
    {
        $this->autorizarPermiso('inventario.ver', 'No tiene permiso para ver inventario');

        $movimientoInventario->load(['material', 'producto', 'usuario', 'compra', 'pedido']);

        return Inertia::render('Inventario/Show', [
            'movimiento' => $movimientoInventario,
        ]);
    }
}

