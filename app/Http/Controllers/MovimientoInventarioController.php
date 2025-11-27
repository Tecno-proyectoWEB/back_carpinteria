<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Material;
use App\Models\Producto;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('inventario.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver inventario'], 403);
            }
            abort(403, 'No tiene permiso para ver inventario');
        }

        $query = MovimientoInventario::with(['material', 'producto', 'usuario', 'compra', 'pedido']);

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('material_id')) {
            $query->where('material_id', $request->material_id);
        }

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('fecha_desde')) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->where('fecha', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->paginate(20));
        }

        // Si es petición web, retornar Inertia con paginación
        $movimientos = $query->paginate($request->get('per_page', 15));
        $materiales = Material::where('activo', true)->get();
        $productos = Producto::all();

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Inventario/Index', [
            'movimientos' => $movimientos,
            'materiales' => $materiales,
            'productos' => $productos,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['tipo', 'material_id', 'producto_id', 'fecha_desde', 'fecha_hasta', 'sort_by', 'sort_dir']),
        ]);
    }

    public function stock(Request $request)
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use GET /api/movimientos-inventario para ver movimientos'], 405);
        }

        if (!Auth::user()->tienePermiso('inventario.ver')) {
            abort(403, 'No tiene permiso para ver inventario');
        }

        $materiales = Material::with('categoria', 'sector')->get();
        $productos = Producto::with('categoria')->get();

        // Alertas de stock bajo
        $alertasMateriales = $materiales->filter(function($m) {
            return $m->stock_actual <= $m->stock_minimo;
        });
        $alertasProductos = $productos->filter(function($p) {
            return $p->stock <= $p->stock_minimo;
        });

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Inventario/Stock', [
            'materiales' => $materiales,
            'productos' => $productos,
            'alertasMateriales' => $alertasMateriales,
            'alertasProductos' => $alertasProductos,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/movimientos-inventario para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('inventario.crear')) {
            abort(403, 'No tiene permiso para crear movimientos de inventario');
        }

        $materiales = Material::where('activo', true)->get();
        $productos = Producto::all();

        return Inertia::render('Inventario/Create', [
            'materiales' => $materiales,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos según tipo
        if ($request->tipo === 'INGRESO' && !Auth::user()->tienePermiso('inventario.ingreso')) {
            return response()->json(['message' => 'No tiene permiso para registrar ingresos'], 403);
        }

        if ($request->tipo === 'SALIDA' && !Auth::user()->tienePermiso('inventario.salida')) {
            return response()->json(['message' => 'No tiene permiso para registrar salidas'], 403);
        }

        $request->validate([
            'tipo' => 'required|in:INGRESO,SALIDA',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'material_id' => 'required_without:producto_id|exists:material,id',
            'producto_id' => 'required_without:material_id|exists:producto,id',
            'compra_id' => 'nullable|exists:compra,id',
            'pedido_id' => 'nullable|exists:pedido,id',
        ]);

        DB::beginTransaction();
        try {
            $movimiento = MovimientoInventario::create([
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'material_id' => $request->material_id,
                'producto_id' => $request->producto_id,
                'compra_id' => $request->compra_id,
                'pedido_id' => $request->pedido_id,
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            // Actualizar stock
            if ($request->material_id) {
                $material = Material::findOrFail($request->material_id);
                if ($request->tipo === 'INGRESO') {
                    $material->stock_actual += $request->cantidad;
                } else {
                    if ($material->stock_actual < $request->cantidad) {
                        DB::rollBack();
                        return response()->json(['error' => 'Stock insuficiente'], 400);
                    }
                    $material->stock_actual -= $request->cantidad;
                }
                $material->save();
            } elseif ($request->producto_id) {
                $producto = Producto::findOrFail($request->producto_id);
                if ($request->tipo === 'INGRESO') {
                    $producto->stock += $request->cantidad;
                } else {
                    if ($producto->stock < $request->cantidad) {
                        DB::rollBack();
                        return response()->json(['error' => 'Stock insuficiente'], 400);
                    }
                    $producto->stock -= $request->cantidad;
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
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            // Si es petición API, retornar JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json($movimiento->load(['material', 'producto', 'usuario']), 201);
            }

            // Si es petición web, redirigir
            return redirect()->route('inventario.index')
                ->with('success', 'Movimiento de inventario creado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Request $request, MovimientoInventario $movimientoInventario)
    {
        $movimientoInventario->load(['material', 'producto', 'usuario', 'compra', 'pedido']);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($movimientoInventario);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Inventario/Show', [
            'movimiento' => $movimientoInventario,
        ]);
    }
}
