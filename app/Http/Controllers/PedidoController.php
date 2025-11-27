<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Pago;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Usuario;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PedidoController extends Controller
{
    public function index()
    {
        return Inertia::render('Pedidos/Index', [
            'pedidos' => Pedido::with(['usuario', 'metodoPago', 'detalles.producto', 'detalles.servicio'])->orderBy('fecha', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Pedidos/Create', [
            'clientes' => Usuario::whereHas('rol', function($q) {
                $q->where('nombre', 'CLIENTE');
            })->get(),
            'productos' => Producto::all(),
            'servicios' => Servicio::where('activo', true)->get(),
            'metodosPago' => MetodoPago::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Este método redirige a storeContado o storeCredito según el tipo
        // Los formularios envían directamente a storeContado o storeCredito
        return redirect()->route('pedidos.create');
    }

    public function storeContado(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear pedidos']);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required_without:detalles.*.servicio_id|exists:producto,id',
            'detalles.*.servicio_id' => 'required_without:detalles.*.producto_id|exists:servicio,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Validar stock disponible para productos
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

                // Crear pedido con estado true (completado)
                $pedido = new Pedido;
                $pedido->fecha = $request->fecha ?? now();
                $pedido->descripcion = $request->descripcion ?? '';
                $pedido->estado = true; // Completado
                $pedido->usuario_id = $request->usuario_id;
                $pedido->metodo_pago_id = $request->metodo_pago_id;
                $pedido->importe_total = 0; // temporal
                $pedido->importe_total_desc = 0; // temporal
                $pedido->save();

                // Crear detalles y actualizar stock
                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetallePedido;
                    $detalle->pedido_id = $pedido->id;
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

                    // Si es producto, actualizar stock y crear movimiento de inventario
                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Crear movimiento de inventario SALIDA
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta al contado',
                            'observaciones' => "Pedido #{$pedido->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);
                    }
                }

                // Actualizar totales en pedido
                $pedido->importe_total = $importe_total;
                $pedido->importe_total_desc = $importe_total_desc;
                $pedido->save();

                // Crear pago inmediato (al contado)
                Pago::create([
                    'monto' => $importe_total_desc,
                    'fecha_pago' => now(),
                    'fecha_vencimiento' => null,
                    'estado' => 'PAGADO',
                    'tipo' => 'CONTADO',
                    'numero_cuota' => null,
                    'observaciones' => 'Pago completo al contado',
                    'pedido_id' => $pedido->id,
                    'metodo_pago_id' => $request->metodo_pago_id,
                    'usuario_id' => Auth::id(),
                ]);

                return redirect()->route('pedidos.index')->with('success', 'Venta al contado registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar venta al contado: ' . $e->getMessage()]);
        }
    }

    public function storeCredito(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear pedidos']);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required_without:detalles.*.servicio_id|exists:producto,id',
            'detalles.*.servicio_id' => 'required_without:detalles.*.producto_id|exists:servicio,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1|max:12',
            'fecha_primera_cuota' => 'required|date|after_or_equal:today',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Validar stock disponible para productos
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

                // Crear pedido con estado false (pendiente pago)
                $pedido = new Pedido;
                $pedido->fecha = $request->fecha ?? now();
                $pedido->descripcion = $request->descripcion ?? '';
                $pedido->estado = false; // Pendiente hasta que se paguen todas las cuotas
                $pedido->usuario_id = $request->usuario_id;
                $pedido->metodo_pago_id = $request->metodo_pago_id;
                $pedido->importe_total = 0; // temporal
                $pedido->importe_total_desc = 0; // temporal
                $pedido->save();

                // Crear detalles
                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetallePedido;
                    $detalle->pedido_id = $pedido->id;
                    $detalle->producto_id = $detalleData['producto_id'] ?? null;
                    $detalle->servicio_id = $detalleData['servicio_id'] ?? null;
                    $detalle->cantidad = $detalleData['cantidad'];
                    $detalle->precio_unitario = $detalleData['precio_unitario'];
                    $detalle->importe_total = $detalle->cantidad * $detalle->precio_unitario;
                    $detalle->importe_total_desc = $detalleData['importe_total_desc'] ?? $detalle->importe_total;
                    $detalle->estado = false; // pendiente
                    $detalle->save();

                    $importe_total += $detalle->importe_total;
                    $importe_total_desc += $detalle->importe_total_desc;
                }

                // Actualizar totales en pedido
                $pedido->importe_total = $importe_total;
                $pedido->importe_total_desc = $importe_total_desc;
                $pedido->save();

                // Crear pagos a crédito (cuotas)
                $monto_cuota = $importe_total_desc / $request->numero_cuotas;
                $fecha_cuota = new \DateTime($request->fecha_primera_cuota);

                for ($i = 1; $i <= $request->numero_cuotas; $i++) {
                    Pago::create([
                        'monto' => $i === $request->numero_cuotas
                            ? $importe_total_desc - ($monto_cuota * ($request->numero_cuotas - 1)) // Última cuota con ajuste
                            : $monto_cuota,
                        'fecha_pago' => null,
                        'fecha_vencimiento' => $fecha_cuota->format('Y-m-d'),
                        'estado' => 'PENDIENTE',
                        'tipo' => 'CUOTA',
                        'numero_cuota' => $i,
                        'observaciones' => "Cuota {$i} de {$request->numero_cuotas}",
                        'pedido_id' => $pedido->id,
                        'metodo_pago_id' => $request->metodo_pago_id,
                        'usuario_id' => null,
                    ]);

                    // Incrementar fecha para siguiente cuota (mensual)
                    $fecha_cuota->modify('+1 month');
                }

                return redirect()->route('pedidos.index')->with('success', 'Venta a crédito registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar venta a crédito: ' . $e->getMessage()]);
        }
    }

    public function confirmarCredito(Pedido $pedido)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.aprobar')) {
            return back()->withErrors(['message' => 'No tiene permiso para confirmar pedidos']);
        }

        if ($pedido->estado) {
            return back()->withErrors(['error' => 'El pedido ya está confirmado']);
        }

        try {
            DB::transaction(function () use ($pedido) {
                // Actualizar stock y crear movimientos de inventario
                foreach ($pedido->detalles as $detalle) {
                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);

                        // Verificar stock
                        if ($producto->stock < $detalle->cantidad) {
                            throw new \Exception("Stock insuficiente para producto {$producto->nombre}");
                        }

                        // Actualizar stock
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Crear movimiento de inventario SALIDA
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta a crédito confirmada',
                            'observaciones' => "Pedido #{$pedido->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);
                    }

                    $detalle->estado = true;
                    $detalle->save();
                }

                $pedido->estado = true;
                $pedido->save();
            });

            return redirect()->route('pedidos.show', $pedido->id)->with('success', 'Pedido a crédito confirmado exitosamente');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al confirmar pedido: ' . $e->getMessage()]);
        }
    }

    public function salidaProducto(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.crear')) {
            return back()->withErrors(['error' => 'No tiene permiso para crear pedidos']);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'descripcion' => 'nullable|string',
            'usuario_id' => 'required|exists:usuario,id',
            'metodo_pago_id' => 'nullable|exists:metodo_pago,id',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:producto,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.importe_total_desc' => 'nullable|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Validar stock disponible para productos
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

                // Crear pedido con estado true (completado o activo)
                $pedido = new Pedido;
                $pedido->fecha = $request->fecha ?? now();
                $pedido->descripcion = $request->descripcion ?? '';
                $pedido->estado = true; // pedido activo/completado
                $pedido->usuario_id = $request->usuario_id;
                $pedido->metodo_pago_id = $request->metodo_pago_id;
                $pedido->importe_total = 0; // temporal
                $pedido->importe_total_desc = 0; // temporal
                $pedido->save();

                $importe_total = 0;
                $importe_total_desc = 0;

                // Agregar detalles y actualizar stock
                foreach ($request->detalles as $detalleData) {
                    $detalle = new DetallePedido;
                    $detalle->pedido_id = $pedido->id;
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

                    // Si es producto, actualizar stock y crear movimiento de inventario
                    if ($detalle->producto_id) {
                        $producto = Producto::findOrFail($detalle->producto_id);
                        $producto->stock -= $detalle->cantidad;
                        $producto->save();

                        // Crear movimiento de inventario SALIDA
                        MovimientoInventario::create([
                            'tipo' => 'SALIDA',
                            'cantidad' => $detalle->cantidad,
                            'motivo' => 'Venta - Salida de producto',
                            'observaciones' => "Pedido #{$pedido->id} - {$producto->nombre}",
                            'material_id' => null,
                            'producto_id' => $producto->id,
                            'pedido_id' => $pedido->id,
                            'usuario_id' => Auth::id(),
                            'fecha' => now(),
                        ]);
                    }
                }

                // Actualizar totales en pedido
                $pedido->importe_total = $importe_total;
                $pedido->importe_total_desc = $importe_total_desc;
                $pedido->save();

                return redirect()->route('pedidos.show', $pedido->id)->with('success', 'Salida de producto registrada exitosamente');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al procesar salida de producto: ' . $e->getMessage()]);
        }
    }

    public function show(Pedido $pedido)
    {
        return Inertia::render('Pedidos/Show', [
            'pedido' => $pedido->load(['usuario', 'metodoPago', 'detalles.producto', 'detalles.servicio', 'pagos']),
        ]);
    }

    public function update(Request $request, Pedido $pedido)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar pedidos']);
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

        $datos_anteriores = $pedido->toArray();
        $pedido->update($request->all());

        return redirect()->route('pedidos.index')->with('success', 'Pedido actualizado exitosamente');
    }

    public function destroy(Pedido $pedido)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pedidos.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar pedidos']);
        }

        if ($pedido->estado) {
            return back()->withErrors(['error' => 'No se puede eliminar un pedido confirmado']);
        }

        $pedido->delete();
        return redirect()->route('pedidos.index')->with('success', 'Pedido eliminado exitosamente');
    }
}
