<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetallePedidoCompra;
use App\Models\MovimientoInventario;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index()
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.ver')) {
            return response()->json(['message' => 'No tiene permiso para ver compras'], 403);
        }

        return response()->json(Compra::with(['proveedor', 'usuario', 'detalles.material'])->get());
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.crear')) {
            return response()->json(['message' => 'No tiene permiso para crear compras'], 403);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'estado' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA',
            'proveedor_id' => 'required|exists:proveedor,id',
            'importe_descuento' => 'nullable|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.material_id' => 'required|exists:material,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio' => 'required|numeric|min:0',
            'detalles.*.importe_desc' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $importe_total = 0;
                $importe_total_desc = 0;

                // Crear compra
                $compra = Compra::create([
                    'fecha' => $request->fecha ?? now(),
                    'estado' => $request->estado,
                    'proveedor_id' => $request->proveedor_id,
                    'usuario_id' => Auth::id(),
                    'importe_total' => 0, // temporal
                    'importe_descuento' => $request->importe_descuento ?? 0,
                ]);

                // Crear detalles y calcular totales
                foreach ($request->detalles as $detalleData) {
                    $detalle = DetallePedidoCompra::create([
                        'compra_id' => $compra->id,
                        'material_id' => $detalleData['material_id'],
                        'cantidad' => $detalleData['cantidad'],
                        'precio' => $detalleData['precio'],
                        'importe' => $detalleData['cantidad'] * $detalleData['precio'],
                        'importe_desc' => $detalleData['importe_desc'] ?? ($detalleData['cantidad'] * $detalleData['precio']),
                        'estado' => $request->estado === 'COMPLETADA' ? 'RECIBIDO' : 'PENDIENTE',
                    ]);

                    $importe_total += $detalle->importe;
                    $importe_total_desc += $detalle->importe_desc;
                }

                // Actualizar totales
                $compra->importe_total = $importe_total - ($compra->importe_descuento ?? 0);
                $compra->save();

                // Si la compra está completada, generar movimientos de inventario automáticamente
                if ($request->estado === 'COMPLETADA') {
                    foreach ($compra->detalles as $detalle) {
                        MovimientoInventario::create([
                            'tipo' => 'INGRESO',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Compra de materiales',
                            'observaciones' => "Compra #{$compra->id} - {$detalle->material->nombre}",
                            'material_id' => $detalle->material_id,
                            'producto_id' => null,
                            'compra_id' => $compra->id,
                            'pedido_id' => null,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);

                        // Actualizar stock del material
                        $material = Material::findOrFail($detalle->material_id);
                        $material->stock_actual += $detalle->cantidad;
                        $material->save();
                    }
                }

                // Registrar en bitácora
                \App\Models\Bitacora::create([
                    'accion' => 'Compra creada',
                    'modulo' => 'Compra',
                    'tabla_afectada' => 'compra',
                    'registro_id' => $compra->id,
                    'datos_nuevos' => $compra->toArray(),
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);

                return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']), 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear compra: ' . $e->getMessage()], 500);
        }
    }

    public function show(Compra $compra)
    {
        return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']));
    }

    public function update(Request $request, Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.editar')) {
            return response()->json(['message' => 'No tiene permiso para editar compras'], 403);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'estado' => 'sometimes|required|in:PENDIENTE,COMPLETADA,CANCELADA',
            'proveedor_id' => 'sometimes|required|exists:proveedor,id',
            'importe_descuento' => 'nullable|numeric|min:0',
        ]);

        $datos_anteriores = $compra->toArray();

        // Si se cambia el estado a COMPLETADA y antes no lo estaba, generar movimientos
        if ($request->estado === 'COMPLETADA' && $compra->estado !== 'COMPLETADA') {
            DB::beginTransaction();
            try {
                foreach ($compra->detalles as $detalle) {
                    // Verificar si ya existe movimiento para este detalle
                    $movimientoExistente = MovimientoInventario::where('compra_id', $compra->id)
                        ->where('material_id', $detalle->material_id)
                        ->first();

                    if (!$movimientoExistente) {
                        MovimientoInventario::create([
                            'tipo' => 'INGRESO',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Compra de materiales',
                            'observaciones' => "Compra #{$compra->id} - {$detalle->material->nombre}",
                            'material_id' => $detalle->material_id,
                            'producto_id' => null,
                            'compra_id' => $compra->id,
                            'pedido_id' => null,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);

                        // Actualizar stock
                        $material = Material::findOrFail($detalle->material_id);
                        $material->stock_actual += $detalle->cantidad;
                        $material->save();
                    }
                }

                // Actualizar estado de detalles
                foreach ($compra->detalles as $detalle) {
                    $detalle->estado = 'RECIBIDO';
                    $detalle->save();
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => 'Error al confirmar compra: ' . $e->getMessage()], 500);
            }
        }

        $compra->update($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Compra actualizada',
            'modulo' => 'Compra',
            'tabla_afectada' => 'compra',
            'registro_id' => $compra->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $compra->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']));
    }

    public function destroy(Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.eliminar')) {
            return response()->json(['message' => 'No tiene permiso para eliminar compras'], 403);
        }

        if ($compra->estado === 'COMPLETADA') {
            return response()->json(['error' => 'No se puede eliminar una compra completada'], 400);
        }

        // Registrar en bitácora antes de eliminar
        \App\Models\Bitacora::create([
            'accion' => 'Compra eliminada',
            'modulo' => 'Compra',
            'tabla_afectada' => 'compra',
            'registro_id' => $compra->id,
            'datos_anteriores' => $compra->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $compra->delete();
        return response()->json(null, 204);
    }

    public function confirmar(Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.editar')) {
            return response()->json(['message' => 'No tiene permiso para confirmar compras'], 403);
        }
    {
        if ($compra->estado === 'COMPLETADA') {
            return response()->json(['error' => 'La compra ya está completada'], 400);
        }

        try {
            return DB::transaction(function () use ($compra) {
                foreach ($compra->detalles as $detalle) {
                    // Verificar si ya existe movimiento
                    $movimientoExistente = MovimientoInventario::where('compra_id', $compra->id)
                        ->where('material_id', $detalle->material_id)
                        ->first();

                    if (!$movimientoExistente) {
                        MovimientoInventario::create([
                            'tipo' => 'INGRESO',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Compra de materiales',
                            'observaciones' => "Compra #{$compra->id} - {$detalle->material->nombre}",
                            'material_id' => $detalle->material_id,
                            'producto_id' => null,
                            'compra_id' => $compra->id,
                            'pedido_id' => null,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);

                        // Actualizar stock
                        $material = Material::findOrFail($detalle->material_id);
                        $material->stock_actual += $detalle->cantidad;
                        $material->save();
                    }

                    $detalle->estado = 'RECIBIDO';
                    $detalle->save();
                }

                $compra->estado = 'COMPLETADA';
                $compra->save();

                // Registrar en bitácora
                \App\Models\Bitacora::create([
                    'accion' => 'Compra confirmada',
                    'modulo' => 'Compra',
                    'tabla_afectada' => 'compra',
                    'registro_id' => $compra->id,
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);

                return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']));
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al confirmar compra: ' . $e->getMessage()], 500);
        }
    }
}
