<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Material;
use App\Models\Producto;
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
            return back()->withErrors(['message' => 'No tiene permiso para ver inventario']);
        }

        $query = MovimientoInventario::with(['material', 'producto', 'usuario', 'venta']);

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

        $movimientos = $query->orderBy('fecha', 'desc')->paginate(20);

        return Inertia::render('Inventarios/Index', [
            'movimientos' => $movimientos,
            'filters' => $request->only(['tipo', 'material_id', 'producto_id', 'fecha_desde', 'fecha_hasta']),
            'materiales' => Material::where('activo', true)->get(['id', 'nombre']),
            'productos' => Producto::all(['id', 'nombre']),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('inventario.ingreso') && !Auth::user()->tienePermiso('inventario.salida')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar movimientos']);
        }

        return Inertia::render('Inventarios/Create', [
            'materiales' => Material::where('activo', true)->get(),
            'productos' => Producto::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos según tipo
        if ($request->tipo === 'INGRESO' && !Auth::user()->tienePermiso('inventario.ingreso')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar ingresos']);
        }

        if ($request->tipo === 'SALIDA' && !Auth::user()->tienePermiso('inventario.salida')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar salidas']);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:INGRESO,SALIDA',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string',
            'venta_id' => 'nullable|exists:venta,id',
        ]);

        // Corrección 5.3: Validar material_id o producto_id manualmente
        if (empty($request->material_id) && empty($request->producto_id)) {
            return back()->withErrors([
                'material_id' => 'Debe seleccionar un material o un producto',
            ])->withInput();
        }

        if (!empty($request->material_id) && !Material::where('id', $request->material_id)->exists()) {
            return back()->withErrors([
                'material_id' => 'El material seleccionado no existe',
            ])->withInput();
        }

        if (!empty($request->producto_id) && !Producto::where('id', $request->producto_id)->exists()) {
            return back()->withErrors([
                'producto_id' => 'El producto seleccionado no existe',
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            $movimiento = MovimientoInventario::create([
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'motivo' => $request->motivo,
                'observaciones' => $request->observaciones,
                'material_id' => $request->material_id ?? null,
                'producto_id' => $request->producto_id ?? null,
                'venta_id' => $request->venta_id ?? null,
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
                        return back()->withErrors(['error' => 'Stock insuficiente']);
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
                        return back()->withErrors(['error' => 'Stock insuficiente']);
                    }
                    $producto->stock -= $request->cantidad;
                }
                $producto->save();
            }

            DB::commit();

            return redirect()->route('inventarios.index')->with('success', 'Movimiento de inventario registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(MovimientoInventario $movimientoInventario)
    {
        return Inertia::render('Inventarios/Show', [
            'movimiento' => $movimientoInventario->load(['material', 'producto', 'usuario', 'venta']),
        ]);
    }
}
