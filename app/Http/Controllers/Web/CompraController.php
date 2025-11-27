<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\Compra;
use App\Models\Material;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompraController extends Controller
{
    use HasPermissions;
    public function index(Request $request)
    {
        $this->autorizarPermiso('compras.ver', 'No tiene permiso para ver compras');

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

        $compras = $query->paginate($request->get('per_page', 15));

        $proveedores = Proveedor::where('activo', true)->get();

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'filters' => $request->only(['estado', 'proveedor_id', 'fecha_desde', 'fecha_hasta', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        $this->autorizarPermiso('compras.crear', 'No tiene permiso para crear compras');

        $materiales = Material::where('activo', true)->get();
        $proveedores = Proveedor::where('activo', true)->get();

        return Inertia::render('Compras/Create', [
            'materiales' => $materiales,
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarPermiso('compras.crear', 'No tiene permiso para crear compras');

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'estado' => 'required|in:PENDIENTE,COMPLETADA,CANCELADA',
            'proveedor_id' => 'required|exists:proveedor,id',
            'importe_descuento' => 'nullable|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.material_id' => 'required|exists:material,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio' => 'required|numeric|min:0',
        ], [
            'estado.required' => 'El estado es obligatorio.',
            'proveedor_id.required' => 'El proveedor es obligatorio.',
            'detalles.required' => 'Debe agregar al menos un material.',
            'detalles.min' => 'Debe agregar al menos un material.',
        ]);

        try {
            $compra = DB::transaction(function () use ($validated) {
                $importe_total = 0;
                $importe_total_desc = 0;

                // Crear compra
                $compra = Compra::create([
                    'fecha' => $validated['fecha'] ?? now(),
                    'estado' => $validated['estado'],
                    'proveedor_id' => $validated['proveedor_id'],
                    'usuario_id' => auth()->id(),
                    'importe_total' => 0,
                    'importe_descuento' => $validated['importe_descuento'] ?? 0,
                ]);

                // Crear detalles
                foreach ($validated['detalles'] as $detalleData) {
                    $detalle = \App\Models\DetallePedidoCompra::create([
                        'compra_id' => $compra->id,
                        'material_id' => $detalleData['material_id'],
                        'cantidad' => $detalleData['cantidad'],
                        'precio' => $detalleData['precio'],
                        'importe' => $detalleData['cantidad'] * $detalleData['precio'],
                        'importe_desc' => $detalleData['importe_desc'] ?? ($detalleData['cantidad'] * $detalleData['precio']),
                        'estado' => $validated['estado'] === 'COMPLETADA' ? 'RECIBIDO' : 'PENDIENTE',
                    ]);

                    $importe_total += $detalle->importe;
                    $importe_total_desc += $detalle->importe_desc;
                }

                // Actualizar totales
                $compra->importe_total = $importe_total - ($compra->importe_descuento ?? 0);
                $compra->save();

                // Si está completada, generar movimientos de inventario
                if ($validated['estado'] === 'COMPLETADA') {
                    foreach ($compra->detalles as $detalle) {
                        \App\Models\MovimientoInventario::create([
                            'tipo' => 'INGRESO',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Compra de materiales',
                            'material_id' => $detalle->material_id,
                            'compra_id' => $compra->id,
                            'usuario_id' => auth()->id(),
                            'fecha' => now(),
                        ]);

                        $material = Material::findOrFail($detalle->material_id);
                        $material->stock_actual += $detalle->cantidad;
                        $material->save();
                    }
                }

                \App\Models\Bitacora::create([
                    'accion' => 'Compra creada',
                    'modulo' => 'Compra',
                    'tabla_afectada' => 'compra',
                    'registro_id' => $compra->id,
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);

                return $compra;
            });

            return redirect()->route('compras.show', $compra->id)
                ->with('success', 'Compra registrada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Compra $compra)
    {
        $this->autorizarPermiso('compras.ver', 'No tiene permiso para ver compras');

        $compra->load(['proveedor', 'usuario', 'detalles.material', 'movimientosInventario']);

        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function confirmar(Compra $compra)
    {
        $this->autorizarPermiso('compras.confirmar', 'No tiene permiso para confirmar compras');

        if ($compra->estado === 'COMPLETADA') {
            return back()->withErrors(['error' => 'La compra ya está completada']);
        }

        try {
            DB::transaction(function () use ($compra) {
                // Generar movimientos de inventario
                foreach ($compra->detalles as $detalle) {
                    $movimientoExistente = \App\Models\MovimientoInventario::where('compra_id', $compra->id)
                        ->where('material_id', $detalle->material_id)
                        ->first();

                    if (!$movimientoExistente) {
                        \App\Models\MovimientoInventario::create([
                            'tipo' => 'INGRESO',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Compra de materiales',
                            'material_id' => $detalle->material_id,
                            'compra_id' => $compra->id,
                            'usuario_id' => auth()->id(),
                            'fecha' => now(),
                        ]);

                        $material = Material::findOrFail($detalle->material_id);
                        $material->stock_actual += $detalle->cantidad;
                        $material->save();
                    }

                    $detalle->estado = 'RECIBIDO';
                    $detalle->save();
                }

                $compra->estado = 'COMPLETADA';
                $compra->save();

                \App\Models\Bitacora::create([
                    'accion' => 'Compra confirmada',
                    'modulo' => 'Compra',
                    'tabla_afectada' => 'compra',
                    'registro_id' => $compra->id,
                    'usuario_id' => auth()->id(),
                    'fecha' => now(),
                ]);
            });

            return redirect()->route('compras.show', $compra->id)
                ->with('success', 'Compra confirmada exitosamente. Stock actualizado.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

