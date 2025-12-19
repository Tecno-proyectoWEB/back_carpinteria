<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Material;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompraController extends BaseController
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('compras.ver')) {
            return $this->respondError('No tiene permiso para ver compras', 403);
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

        $sortBy = $request->get('sort_by', 'fecha');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        if ($this->isApiRequest($request)) {
            return response()->json($query->get());
        }

        $compras = $query->paginate($request->get('per_page', 15));

        return $this->respond($compras, 'Compras/Index', [
            'compras' => $compras,
            'proveedores' => Proveedor::where('activo', true)->get(),
            'filters' => $request->only(['estado', 'proveedor_id', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('compras.crear')) {
            return $this->respondError('No tiene permiso para crear compras', 403);
        }

        $request->validate([
            'proveedor_id' => 'required|exists:proveedor,id',
            'fecha' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.material_id' => 'required|exists:material,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Calcular totales
            $importeTotal = 0;
            $importeDescuento = 0;

            foreach ($request->detalles as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                $descuento = $detalle['descuento'] ?? 0;
                $importeTotal += ($subtotal - $descuento);
                $importeDescuento += $descuento;
            }

            // Crear compra
            $compra = Compra::create([
                'proveedor_id' => $request->proveedor_id,
                'usuario_id' => Auth::id(),
                'fecha' => $request->fecha ?? now(),
                'observaciones' => $request->observaciones,
                'importe_total' => $importeTotal,
                'importe_descuento' => $importeDescuento,
                'estado' => 'COMPLETADA',
            ]);

            // Crear detalles y actualizar inventario
            foreach ($request->detalles as $detalle) {
                $subtotal = $detalle['cantidad'] * $detalle['precio_unitario'];
                $descuento = $detalle['descuento'] ?? 0;
                $importeTotal = $subtotal - $descuento;

                DetalleCompra::create([
                    'compra_id' => $compra->id,
                    'material_id' => $detalle['material_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $subtotal,
                    'descuento' => $descuento,
                    'importe_total' => $importeTotal,
                ]);

                // Actualizar stock del material
                $material = Material::findOrFail($detalle['material_id']);
                $stockAnterior = $material->stock_actual;
                $material->stock_actual += $detalle['cantidad'];
                $material->save();

                // Registrar movimiento de inventario
                MovimientoInventario::create([
                    'material_id' => $material->id,
                    'tipo_movimiento' => 'ENTRADA',
                    'cantidad' => $detalle['cantidad'],
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $material->stock_actual,
                    'motivo' => 'Compra #' . $compra->id,
                    'fecha' => now(),
                    'usuario_id' => Auth::id(),
                ]);
            }

            // Registrar en bitácora
            Bitacora::create([
                'accion' => 'Compra registrada',
                'modulo' => 'Compras',
                'tabla_afectada' => 'compra',
                'registro_id' => $compra->id,
                'datos_nuevos' => [
                    'proveedor_id' => $compra->proveedor_id,
                    'importe_total' => $compra->importe_total,
                    'items' => count($request->detalles),
                ],
                'usuario_id' => Auth::id(),
                'direccion_ip' => $request->ip(),
                'navegador' => $request->userAgent(),
                'fecha' => now(),
            ]);

            DB::commit();

            $compra->load(['proveedor', 'detalles.material']);

            if ($this->isApiRequest($request)) {
                return response()->json($compra, 201);
            }

            return redirect()->route('compras.show', $compra)
                ->with('success', 'Compra registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($this->isApiRequest($request)) {
                return response()->json(['message' => 'Error al registrar compra: ' . $e->getMessage()], 500);
            }

            return back()->withErrors(['error' => 'Error al registrar compra: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Request $request, Compra $compra)
    {
        if (!Auth::user()->tienePermiso('compras.ver')) {
            return $this->respondError('No tiene permiso para ver compras', 403);
        }

        $compra->load(['proveedor', 'usuario', 'detalles.material', 'movimientosInventario']);

        return $this->respond($compra, 'Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function update(Request $request, Compra $compra)
    {
        if (!Auth::user()->tienePermiso('compras.editar')) {
            return $this->respondError('No tiene permiso para editar compras', 403);
        }

        $datosAnteriores = $compra->toArray();

        $request->validate([
            'estado' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA',
            'observaciones' => 'nullable|string',
        ]);

        $compra->update($request->only(['estado', 'observaciones']));

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Compra actualizada',
            'modulo' => 'Compras',
            'tabla_afectada' => 'compra',
            'registro_id' => $compra->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $request->only(['estado', 'observaciones']),
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json($compra);
        }

        return redirect()->route('compras.show', $compra)
            ->with('success', 'Compra actualizada exitosamente');
    }
}
