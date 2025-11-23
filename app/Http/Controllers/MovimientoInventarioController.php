<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Material;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('inventario.ver')) {
            return response()->json(['message' => 'No tiene permiso para ver inventario'], 403);
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

        return response()->json($query->orderBy('fecha', 'desc')->paginate(20));
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

            return response()->json($movimiento->load(['material', 'producto', 'usuario']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(MovimientoInventario $movimientoInventario)
    {
        return response()->json($movimientoInventario->load(['material', 'producto', 'usuario', 'compra', 'pedido']));
    }
}
