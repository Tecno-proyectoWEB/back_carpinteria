<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetallePedidoCompra;
use App\Models\MovimientoInventario;
use App\Models\Material;
use App\Models\Proveedor;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver compras'], 403);
            }
            abort(403, 'No tiene permiso para ver compras');
        }

        $query = Compra::with(['proveedor', 'usuario', 'detalles.material']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $compras = $query->paginate($request->get('per_page', 15));
        $proveedores = Proveedor::where('activo', true)->get();

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
            'filters' => $request->only(['estado', 'proveedor_id', 'fecha_desde', 'fecha_hasta', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        // Solo para web
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/compras para crear'], 405);
        }

        if (!Auth::user()->tienePermiso('compras.crear')) {
            abort(403, 'No tiene permiso para crear compras');
        }

        $materiales = Material::where('activo', true)->get();
        $proveedores = Proveedor::where('activo', true)->get();

        return Inertia::render('Compras/Create', [
            'materiales' => $materiales,
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear compras'], 403);
            }
            abort(403, 'No tiene permiso para crear compras');
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

                // Si es petición API, retornar JSON
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']), 201);
                }

                // Si es petición web, redirigir
                return redirect()->route('compras.show', $compra->id)
                    ->with('success', 'Compra creada exitosamente');
            });
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Error al crear compra: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Error al crear compra: ' . $e->getMessage()]);
        }
    }

    public function show(Request $request, Compra $compra)
    {
        $compra->load(['proveedor', 'usuario', 'detalles.material']);

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($compra);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function update(Request $request, Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar compras'], 403);
            }
            abort(403, 'No tiene permiso para editar compras');
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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']));
        }

        // Si es petición web, redirigir
        return redirect()->route('compras.index')
            ->with('success', 'Compra actualizada exitosamente');
    }

    public function destroy(Request $request, Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar compras'], 403);
            }
            abort(403, 'No tiene permiso para eliminar compras');
        }

        if ($compra->estado === 'COMPLETADA') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'No se puede eliminar una compra completada'], 400);
            }
            return back()->withErrors(['error' => 'No se puede eliminar una compra completada']);
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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        // Si es petición web, redirigir
        return redirect()->route('compras.index')
            ->with('success', 'Compra eliminada exitosamente');
    }

    public function confirmar(Request $request, Compra $compra)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('compras.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para confirmar compras'], 403);
            }
            abort(403, 'No tiene permiso para confirmar compras');
        }

        if ($compra->estado === 'COMPLETADA') {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'La compra ya está completada'], 400);
            }
            return back()->withErrors(['error' => 'La compra ya está completada']);
        }

        try {
            return DB::transaction(function () use ($request, $compra) {
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

                // Si es petición API, retornar JSON
                if ($request->wantsJson() || $request->is('api/*')) {
                    return response()->json($compra->load(['proveedor', 'usuario', 'detalles.material']));
                }

                // Si es petición web, redirigir
                return redirect()->route('compras.show', $compra->id)
                    ->with('success', 'Compra confirmada exitosamente');
            });
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Error al confirmar compra: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Error al confirmar compra: ' . $e->getMessage()]);
        }
    }
}
