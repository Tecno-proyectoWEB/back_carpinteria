<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Pago;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\MetodoPago;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index()
    {
        return Inertia::render('Ventas/Index', [
            'ventas' => Venta::with(['usuario', 'metodoPago', 'detalles.producto', 'detalles.servicio'])->orderBy('fecha', 'desc')->get(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('ventas.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear ventas']);
        }

        return Inertia::render('Ventas/Create', [
            'clientes' => Usuario::whereHas('rol', function($q) {
                $q->where('nombre', 'CLIENTE');
            })->get(),
            'productos' => Producto::all(),
            'servicios' => Servicio::where('activo', true)->get(),
            'metodosPago' => MetodoPago::where('activo', true)
                ->where(function($q) {
                    $q->where('nombre', 'EFECTIVO')
                      ->orWhere(function($q2) {
                          $q2->where('es_electronico', true)
                             ->where('tipo_electronico', 'QR');
                      });
                })
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        return redirect()->route('ventas.create');
    }

    public function storeContado(Request $request)
    {
        if (!Auth::user()->tienePermiso('ventas.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear ventas']);
        }

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
        ]);

        // Validar producto_id o servicio_id manualmente
        foreach ($request->detalles as $index => $detalle) {
            if (empty($detalle['producto_id']) && empty($detalle['servicio_id'])) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'Debe seleccionar un producto o un servicio',
                ])->withInput();
            }
            
            if (!empty($detalle['producto_id']) && !\App\Models\Producto::where('id', $detalle['producto_id'])->exists()) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'El producto seleccionado no existe',
                ])->withInput();
            }
            
            if (!empty($detalle['servicio_id']) && !\App\Models\Servicio::where('id', $detalle['servicio_id'])->exists()) {
                return back()->withErrors([
                    "detalles.{$index}.servicio_id" => 'El servicio seleccionado no existe',
                ])->withInput();
            }
        }

        try {
            return DB::transaction(function () use ($request) {
                foreach ($request->detalles as $detalleData) {
                    if (isset($detalleData['producto_id'])) {
                        $producto = Producto::find($detalleData['producto_id']);
                        if (!$producto) {
                            throw new \Exception("Producto con id {$detalleData['producto_id']} no encontrado");
                        }
                        if ($producto->stock < $detalleData['cantidad']) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }
                    }
                }

                $importe_total = 0;
                $importe_total_desc = 0;

                $venta = new Venta;
                $venta->fecha = $request->fecha ?? now();
                $venta->descripcion = $request->descripcion ?? '';
                $venta->estado = true;
                $venta->usuario_id = $request->usuario_id;
                $venta->metodo_pago_id = $request->metodo_pago_id;
                $venta->importe_total = 0;
                $venta->importe_total_desc = 0;
                $venta->save();

                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetalleVenta;
                    $detalle->venta_id = $venta->id;
                    $detalle->producto_id = $detalleData['producto_id'] ?? null;
                    $detalle->servicio_id = $detalleData['servicio_id'] ?? null;
                    $detalle->cantidad = $detalleData['cantidad'];
                    $detalle->precio_unitario = $detalleData['precio_unitario'];
                    $detalle->importe_total = $detalle->cantidad * $detalle->precio_unitario;
                    $detalle->importe_total_desc = $detalleData['importe_total_desc'] ?? $detalle->importe_total;
                    $detalle->estado = true;
                    $detalle->save();

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;

                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Obtener el ID del usuario correctamente (no el email)
                        $usuarioId = $request->user()?->id ?? Auth::user()?->id;
                        
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta al contado',
                            'observaciones' => "Venta #{$venta->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'venta_id' => $venta->id,
                            'usuario_id' => $usuarioId,
                            'fecha' => now(),
                        ]);
                    }
                }

                $venta->importe_total = $importe_total;
                $venta->importe_total_desc = $importe_total_desc;
                $venta->save();

                $metodoPago = MetodoPago::find($request->metodo_pago_id);
                $usuario = Usuario::find($request->usuario_id);

                // Obtener el ID del usuario correctamente (no el email)
                $usuarioId = $request->user()?->id ?? Auth::user()?->id;
                
                $pago = Pago::create([
                    'monto' => $importe_total_desc,
                    'fecha_pago' => null,
                    'fecha_vencimiento' => null,
                    'estado' => 'PENDIENTE',
                    'tipo' => 'CONTADO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago completo al contado',
                    'venta_id' => $venta->id,
                    'metodo_pago_id' => $request->metodo_pago_id,
                    'usuario_id' => $usuarioId,
                ]);

                if ($metodoPago && !$metodoPago->es_electronico) {
                    $pago->estado = 'PAGADO';
                    $pago->fecha_pago = now();
                    $pago->fecha_confirmacion = now();
                    $pago->save();
                    $venta->estado = true;
                    $venta->save();
                    return redirect()->route('ventas.index')->with('success', 'Venta al contado registrada exitosamente');
                }

                if ($metodoPago && $metodoPago->es_electronico && $metodoPago->tipo_electronico === 'QR') {
                    $paymentGatewayService = app(PaymentGatewayService::class);
                    $result = $paymentGatewayService->processQRPayment($venta, $pago, $usuario);

                    return redirect()->route('ventas.show', $venta->id)->with([
                        'success' => 'QR generado exitosamente. Escanea el código para pagar.',
                        'qr_generated' => true,
                        'pago_id' => $pago->id
                    ]);
                }

                return redirect()->route('ventas.index')->with('success', 'Venta al contado registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar venta al contado: ' . $e->getMessage()]);
        }
    }

    public function storeCredito(Request $request)
    {
        if (!Auth::user()->tienePermiso('ventas.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear ventas']);
        }

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1|max:12',
            'fecha_primera_cuota' => 'required|date|after_or_equal:today',
        ]);

        // Validar producto_id o servicio_id manualmente
        foreach ($request->detalles as $index => $detalle) {
            if (empty($detalle['producto_id']) && empty($detalle['servicio_id'])) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'Debe seleccionar un producto o un servicio',
                ])->withInput();
            }
            
            if (!empty($detalle['producto_id']) && !\App\Models\Producto::where('id', $detalle['producto_id'])->exists()) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'El producto seleccionado no existe',
                ])->withInput();
            }
            
            if (!empty($detalle['servicio_id']) && !\App\Models\Servicio::where('id', $detalle['servicio_id'])->exists()) {
                return back()->withErrors([
                    "detalles.{$index}.servicio_id" => 'El servicio seleccionado no existe',
                ])->withInput();
            }
        }

        try {
            return DB::transaction(function () use ($request) {
                foreach ($request->detalles as $detalleData) {
                    if (isset($detalleData['producto_id'])) {
                        $producto = Producto::find($detalleData['producto_id']);
                        if (!$producto) {
                            throw new \Exception("Producto con id {$detalleData['producto_id']} no encontrado");
                        }
                        if ($producto->stock < $detalleData['cantidad']) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }
                    }
                }

                $importe_total = 0;
                $importe_total_desc = 0;

                $venta = new Venta;
                $venta->fecha = $request->fecha ?? now();
                $venta->descripcion = $request->descripcion ?? '';
                $venta->estado = false;
                $venta->usuario_id = $request->usuario_id;
                $venta->metodo_pago_id = $request->metodo_pago_id;
                $venta->importe_total = 0;
                $venta->importe_total_desc = 0;
                $venta->save();

                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetalleVenta;
                    $detalle->venta_id = $venta->id;
                    $detalle->producto_id = $detalleData['producto_id'] ?? null;
                    $detalle->servicio_id = $detalleData['servicio_id'] ?? null;
                    $detalle->cantidad = $detalleData['cantidad'];
                    $detalle->precio_unitario = $detalleData['precio_unitario'];
                    $detalle->importe_total = $detalle->cantidad * $detalle->precio_unitario;
                    $detalle->importe_total_desc = $detalleData['importe_total_desc'] ?? $detalle->importe_total;
                    $detalle->estado = false;
                    $detalle->save();

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;
                }

                $venta->importe_total = $importe_total;
                $venta->importe_total_desc = $importe_total_desc;
                $venta->save();

                $metodoPago = MetodoPago::find($request->metodo_pago_id);
                $usuario = Usuario::find($request->usuario_id);

                $monto_cuota = $importe_total_desc / $request->numero_cuotas;
                $fecha_cuota = new \DateTime($request->fecha_primera_cuota);
                $pagosCreados = [];
                $paymentGatewayService = null;

                // Si el método de pago es QR, inicializar el servicio
                if ($metodoPago && $metodoPago->es_electronico && $metodoPago->tipo_electronico === 'QR') {
                    $paymentGatewayService = app(PaymentGatewayService::class);
                }

                for ($i = 1; $i <= $request->numero_cuotas; $i++) {
                    $pago = Pago::create([
                        'monto' => $i === $request->numero_cuotas
                            ? $importe_total_desc - ($monto_cuota * ($request->numero_cuotas - 1))
                            : $monto_cuota,
                        'fecha_pago' => null,
                        'fecha_vencimiento' => $fecha_cuota->format('Y-m-d'),
                        'estado' => 'PENDIENTE',
                        'tipo' => 'CUOTA',
                        'numero_cuota' => $i,
                        'observaciones' => "Cuota {$i} de {$request->numero_cuotas}",
                        'venta_id' => $venta->id,
                        'metodo_pago_id' => $request->metodo_pago_id,
                        'usuario_id' => null,
                    ]);

                    $pagosCreados[] = $pago;

                    // Si el método de pago es QR, generar QR para cada cuota
                    if ($paymentGatewayService) {
                        try {
                            $result = $paymentGatewayService->processQRPayment($venta, $pago, $usuario);
                            $pago->refresh();
                            Log::info("QR generado para cuota {$i}", ['pago_id' => $pago->id]);
                        } catch (\Exception $e) {
                            Log::error("Error al generar QR para cuota {$i}: " . $e->getMessage(), [
                                'pago_id' => $pago->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    $fecha_cuota->modify('+1 month');
                }

                // Si se generaron QRs, redirigir a la vista de la venta
                if ($paymentGatewayService && count($pagosCreados) > 0) {
                    $primerPago = $pagosCreados[0];
                    $qrsGenerados = count(array_filter($pagosCreados, function($p) {
                        return $p->qr_image !== null;
                    }));

                    return redirect()->route('ventas.show', $venta->id)->with([
                        'success' => "Venta a crédito registrada. Se generaron {$qrsGenerados} QR(s) para las cuotas. Escanea el QR para pagar.",
                        'qr_generated' => true,
                        'pago_id' => $primerPago->id
                    ]);
                }

                return redirect()->route('ventas.index')->with('success', 'Venta a crédito registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar venta a crédito: ' . $e->getMessage()]);
        }
    }

    public function confirmarCredito(Venta $venta)
    {
        if (!Auth::user()->tienePermiso('ventas.aprobar')) {
            return back()->withErrors(['message' => 'No tiene permiso para confirmar ventas']);
        }

        if ($venta->estado) {
            return back()->withErrors(['error' => 'La venta ya está confirmada']);
        }

        try {
            DB::transaction(function () use ($venta) {
                foreach ($venta->detalles as $detalle) {
                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        if ($producto->stock < $detalle->cantidad) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Obtener el ID del usuario correctamente (no el email)
                        $usuarioId = Auth::user()?->id;
                        
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta a crédito confirmada',
                            'observaciones' => "Venta #{$venta->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'venta_id' => $venta->id,
                            'usuario_id' => $usuarioId,
                            'fecha' => now(),
                        ]);

                        $detalle->estado = true;
                        $detalle->save();
                    }
                }

                $venta->estado = true;
                $venta->save();
            });

            return redirect()->route('ventas.index')->with('success', 'Venta a crédito confirmada exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al confirmar venta: ' . $e->getMessage()]);
        }
    }

    public function salidaProducto(Request $request)
    {
        if (!Auth::user()->tienePermiso('ventas.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear ventas']);
        }

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
        ]);

        // Validar producto_id manualmente
        foreach ($request->detalles as $index => $detalle) {
            if (empty($detalle['producto_id'])) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'Debe seleccionar un producto',
                ])->withInput();
            }
            
            if (!\App\Models\Producto::where('id', $detalle['producto_id'])->exists()) {
                return back()->withErrors([
                    "detalles.{$index}.producto_id" => 'El producto seleccionado no existe',
                ])->withInput();
            }
        }

        try {
            return DB::transaction(function () use ($request) {
                foreach ($request->detalles as $detalleData) {
                    $producto = Producto::find($detalleData['producto_id']);
                    if (!$producto) {
                        throw new \Exception("Producto con id {$detalleData['producto_id']} no encontrado");
                    }
                    if ($producto->stock < $detalleData['cantidad']) {
                        throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                    }
                }

                $importe_total = 0;
                $importe_total_desc = 0;

                $venta = new Venta;
                $venta->fecha = $request->fecha ?? now();
                $venta->descripcion = $request->descripcion ?? '';
                $venta->estado = true;
                $venta->usuario_id = $request->usuario_id;
                $venta->metodo_pago_id = $request->metodo_pago_id;
                $venta->importe_total = 0;
                $venta->importe_total_desc = 0;
                $venta->save();

                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetalleVenta;
                    $detalle->venta_id = $venta->id;
                    $detalle->producto_id = $detalleData['producto_id'];
                    $detalle->servicio_id = null;
                    $detalle->cantidad = $detalleData['cantidad'];
                    $detalle->precio_unitario = $detalleData['precio_unitario'];
                    $detalle->importe_total = $detalle->cantidad * $detalle->precio_unitario;
                    $detalle->importe_total_desc = $detalleData['importe_total_desc'] ?? $detalle->importe_total;
                    $detalle->estado = true;
                    $detalle->save();

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;

                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Obtener el ID del usuario correctamente (no el email)
                        $usuarioId = $request->user()?->id ?? Auth::user()?->id;
                        
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta - Salida de producto',
                            'observaciones' => "Venta #{$venta->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'venta_id' => $venta->id,
                            'usuario_id' => $usuarioId,
                            'fecha' => now(),
                        ]);
                    }
                }

                $venta->importe_total = $importe_total;
                $venta->importe_total_desc = $importe_total_desc;
                $venta->save();

                return redirect()->route('ventas.show', $venta->id)->with('success', 'Salida de producto registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar salida de producto: ' . $e->getMessage()]);
        }
    }

    public function show(Venta $venta)
    {
        // Cargar relaciones de forma eficiente
        $venta->load([
            'usuario',
            'metodoPago',
            'detalles.producto',
            'detalles.servicio',
            'pagos' => function($query) {
                $query->orderBy('numero_cuota', 'asc');
            }
        ]);

        // Obtener todos los pagos con QR pendientes de forma eficiente
        $pagosConQR = collect($venta->pagos)
            ->filter(function($pago) {
                return $pago->estado === 'PENDIENTE' && !empty($pago->qr_image);
            })
            ->values()
            ->all();

        // Para compatibilidad con el frontend, pasar el primer pago con QR
        $pagoConQR = !empty($pagosConQR) ? $pagosConQR[0] : null;

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'pagoConQR' => $pagoConQR,
            'pagosConQR' => $pagosConQR, // Todos los pagos con QR
        ]);
    }

    public function update(Request $request, Venta $venta)
    {
        if (!Auth::user()->tienePermiso('ventas.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar ventas']);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'importe_total' => 'nullable|numeric|min:0',
            'importe_total_desc' => 'nullable|numeric|min:0',
            'estado' => 'boolean',
            'metodo_pago_id' => 'sometimes|required|exists:metodo_pago,id',
            'usuario_id' => 'sometimes|required|exists:usuario,id',
        ]);

        $venta->update($request->all());

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada exitosamente');
    }

    public function destroy(Venta $venta)
    {
        if (!Auth::user()->tienePermiso('ventas.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar ventas']);
        }

        $venta->delete();

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada exitosamente');
    }
}

