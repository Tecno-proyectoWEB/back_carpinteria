<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{
    use HasPermissions;
    public function index(Request $request)
    {
        $this->autorizarPermiso('pedidos.ver', 'No tiene permiso para ver pedidos');

        $query = Pedido::with(['usuario', 'metodoPago', 'detalles.producto', 'detalles.servicio']);

        // Si es cliente, solo ver sus pedidos
        if (auth()->user()->rol->nombre === 'CLIENTE') {
            $query->where('usuario_id', auth()->id());
        }

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado === 'completado');
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

        $pedidos = $query->paginate($request->get('per_page', 15));

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos,
            'filters' => $request->only(['estado', 'fecha_desde', 'fecha_hasta', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create()
    {
        $this->autorizarPermiso('pedidos.crear', 'No tiene permiso para crear pedidos');

        $productos = Producto::where('stock', '>', 0)->get();
        $servicios = Servicio::where('activo', true)->get();
        $clientes = Usuario::whereHas('rol', function($q) {
            $q->where('nombre', 'CLIENTE');
        })->where('estado', true)->get();
        $metodosPago = MetodoPago::all();

        return Inertia::render('Pedidos/Create', [
            'productos' => $productos,
            'servicios' => $servicios,
            'clientes' => $clientes,
            'metodosPago' => $metodosPago,
        ]);
    }

    public function storeContado(Request $request)
    {
        $this->autorizarPermiso('pedidos.crear', 'No tiene permiso para crear pedidos');

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required_without:detalles.*.servicio_id|nullable|exists:producto,id',
            'detalles.*.servicio_id' => 'required_without:detalles.*.producto_id|nullable|exists:servicio,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
        ], [
            'usuario_id.required' => 'El cliente es obligatorio.',
            'metodo_pago_id.required' => 'El método de pago es obligatorio.',
            'detalles.required' => 'Debe agregar al menos un producto o servicio.',
            'detalles.min' => 'Debe agregar al menos un producto o servicio.',
        ]);

        try {
            $pedido = DB::transaction(function () use ($validated) {
                // Validar stock
                foreach ($validated['detalles'] as $detalle) {
                    if (isset($detalle['producto_id']) && $detalle['producto_id']) {
                        $producto = \App\Models\Producto::find($detalle['producto_id']);
                        if (!$producto || $producto->stock < $detalle['cantidad']) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }
                    }
                }

                // Crear pedido
                $pedido = \App\Models\Pedido::create([
                    'fecha' => $validated['fecha'] ?? now(),
                    'descripcion' => $validated['descripcion'] ?? '',
                    'estado' => true,
                    'usuario_id' => $validated['usuario_id'],
                    'metodo_pago_id' => $validated['metodo_pago_id'],
                    'importe_total' => 0,
                    'importe_total_desc' => 0,
                ]);

                $importe_total = 0;
                $importe_total_desc = 0;

                // Crear detalles
                foreach ($validated['detalles'] as $detalleData) {
                    $detalle = \App\Models\DetallePedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $detalleData['producto_id'] ?? null,
                        'servicio_id' => $detalleData['servicio_id'] ?? null,
                        'cantidad' => $detalleData['cantidad'],
                        'precio_unitario' => $detalleData['precio_unitario'],
                        'importe_total' => $detalleData['cantidad'] * $detalleData['precio_unitario'],
                        'importe_total_desc' => $detalleData['importe_total_desc'] ?? ($detalleData['cantidad'] * $detalleData['precio_unitario']),
                        'estado' => true,
                    ]);

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;

                    // Actualizar stock si es producto
                    if ($detalle->producto_id) {
                        $producto = \App\Models\Producto::findOrFail($detalle->producto_id);
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        \App\Models\MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta al contado',
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'usuario_id' => auth()->id(),
                            'fecha' => now(),
                        ]);
                    }
                }

                // Actualizar totales
                $pedido->importe_total = $importe_total;
                $pedido->importe_total_desc = $importe_total_desc;
                $pedido->save();

                // Crear pago
                \App\Models\Pago::create([
                    'monto' => $importe_total_desc,
                    'fecha_pago' => now(),
                    'estado' => 'PAGADO',
                    'tipo' => 'CONTADO',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $validated['metodo_pago_id'],
                ]);

                // Bitácora
                \App\Models\Bitacora::create([
                    'accion' => 'Venta al contado creada',
                    'modulo' => 'Pedido',
                    'tabla_afectada' => 'pedido',
                    'registro_id' => $pedido->id,
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);

                return $pedido;
            });

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', 'Venta al contado registrada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeCredito(Request $request)
    {
        $this->autorizarPermiso('pedidos.crear', 'No tiene permiso para crear pedidos');

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required_without:detalles.*.servicio_id|nullable|exists:producto,id',
            'detalles.*.servicio_id' => 'required_without:detalles.*.producto_id|nullable|exists:servicio,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1|max:12',
            'fecha_primera_cuota' => 'required|date|after_or_equal:today',
        ], [
            'usuario_id.required' => 'El cliente es obligatorio.',
            'metodo_pago_id.required' => 'El método de pago es obligatorio.',
            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.min' => 'El número mínimo de cuotas es 1.',
            'numero_cuotas.max' => 'El número máximo de cuotas es 12.',
            'fecha_primera_cuota.required' => 'La fecha de la primera cuota es obligatoria.',
            'fecha_primera_cuota.after_or_equal' => 'La fecha de la primera cuota debe ser hoy o posterior.',
        ]);

        try {
            $pedido = DB::transaction(function () use ($validated) {
                // Validar stock
                foreach ($validated['detalles'] as $detalle) {
                    if (isset($detalle['producto_id']) && $detalle['producto_id']) {
                        $producto = \App\Models\Producto::find($detalle['producto_id']);
                        if (!$producto || $producto->stock < $detalle['cantidad']) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }
                    }
                }

                // Crear pedido (pendiente)
                $pedido = \App\Models\Pedido::create([
                    'fecha' => $validated['fecha'] ?? now(),
                    'descripcion' => $validated['descripcion'] ?? '',
                    'estado' => false, // Pendiente hasta pagar todas las cuotas
                    'usuario_id' => $validated['usuario_id'],
                    'metodo_pago_id' => $validated['metodo_pago_id'],
                    'importe_total' => 0,
                    'importe_total_desc' => 0,
                ]);

                $importe_total = 0;
                $importe_total_desc = 0;

                // Crear detalles
                foreach ($validated['detalles'] as $detalleData) {
                    $detalle = \App\Models\DetallePedido::create([
                        'pedido_id' => $pedido->id,
                        'producto_id' => $detalleData['producto_id'] ?? null,
                        'servicio_id' => $detalleData['servicio_id'] ?? null,
                        'cantidad' => $detalleData['cantidad'],
                        'precio_unitario' => $detalleData['precio_unitario'],
                        'importe_total' => $detalleData['cantidad'] * $detalleData['precio_unitario'],
                        'importe_total_desc' => $detalleData['importe_total_desc'] ?? ($detalleData['cantidad'] * $detalleData['precio_unitario']),
                        'estado' => false, // Pendiente
                    ]);

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;
                }

                // Actualizar totales
                $pedido->importe_total = $importe_total;
                $pedido->importe_total_desc = $importe_total_desc;
                $pedido->save();

                // Crear pagos (cuotas)
                $montoCuota = $importe_total_desc / $validated['numero_cuotas'];
                $fechaPrimeraCuota = new \DateTime($validated['fecha_primera_cuota']);

                for ($i = 1; $i <= $validated['numero_cuotas']; $i++) {
                    $fechaVencimiento = clone $fechaPrimeraCuota;
                    $fechaVencimiento->modify('+' . ($i - 1) . ' month');

                    // Ajustar última cuota
                    $monto = ($i === $validated['numero_cuotas'])
                        ? $importe_total_desc - ($montoCuota * ($validated['numero_cuotas'] - 1))
                        : $montoCuota;

                    \App\Models\Pago::create([
                        'monto' => $monto,
                        'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                        'estado' => 'PENDIENTE',
                        'tipo' => 'CUOTA',
                        'numero_cuota' => $i,
                        'pedido_id' => $pedido->id,
                        'metodo_pago_id' => $validated['metodo_pago_id'],
                    ]);
                }

                // Bitácora
                \App\Models\Bitacora::create([
                    'accion' => 'Venta a crédito creada',
                    'modulo' => 'Pedido',
                    'tabla_afectada' => 'pedido',
                    'registro_id' => $pedido->id,
                    'usuario_id' => Auth::id(),
                    'fecha' => now(),
                ]);

                return $pedido;
            });

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', 'Venta a crédito registrada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Pedido $pedido)
    {
        $this->autorizarPermiso('pedidos.ver', 'No tiene permiso para ver pedidos');

        // Si es cliente, solo puede ver sus propios pedidos
        if (auth()->user()->rol->nombre === 'CLIENTE' && $pedido->usuario_id !== auth()->id()) {
            abort(403, 'No tiene permiso para ver este pedido');
        }

        $pedido->load(['usuario', 'metodoPago', 'detalles.producto', 'detalles.servicio', 'pagos']);

        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido,
        ]);
    }
}

