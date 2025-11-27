<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver pagos']);
        }

        $query = Pago::with(['venta', 'metodoPago', 'usuario']);

        if ($request->has('venta_id')) {
            $query->where('venta_id', $request->venta_id);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(20);

        return Inertia::render('Pagos/Index', [
            'pagos' => $pagos,
            'filters' => $request->only(['venta_id', 'estado', 'tipo']),
        ]);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar pagos']);
        }

        // Solo mostrar métodos de pago: EFECTIVO y QR (PagoFácil)
        $metodosPago = MetodoPago::where('activo', true)
            ->where(function($q) {
                $q->where('es_electronico', false)
                  ->orWhere(function($q2) {
                      $q2->where('es_electronico', true)
                         ->where('tipo_electronico', 'QR');
                  });
            })
            ->get();

        return Inertia::render('Pagos/Create', [
            'ventas' => Venta::where('estado', false)->with('usuario')->get(),
            'metodosPago' => $metodosPago,
            'venta_id' => $request->venta_id,
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar pagos']);
        }

        $request->validate([
            'monto' => 'required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:today',
            'estado' => 'required|in:PENDIENTE,PAGADO,VENCIDO,CANCELADO',
            'tipo' => 'required|in:CONTADO,CREDITO,CUOTA',
            'numero_cuota' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'venta_id' => 'required|exists:venta,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
        ]);

        // Obtener el ID del usuario correctamente (no el email)
        $usuarioId = $request->user()?->id ?? Auth::user()?->id;

        $pago = Pago::create([
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago ?? ($request->estado === 'PAGADO' ? now() : null),
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
            'tipo' => $request->tipo,
            'numero_cuota' => $request->numero_cuota,
            'observaciones' => $request->observaciones,
            'venta_id' => $request->venta_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'usuario_id' => $usuarioId,
        ]);

        // Si el método de pago es QR y el estado es PENDIENTE, generar QR
        $metodoPago = MetodoPago::find($request->metodo_pago_id);
        if ($metodoPago && $metodoPago->es_electronico && $metodoPago->tipo_electronico === 'QR' && $request->estado === 'PENDIENTE') {
            try {
                $venta = Venta::find($request->venta_id);
                $usuario = $venta ? $venta->usuario : null;

                if ($venta && $usuario) {
                    $paymentGatewayService = app(\App\Services\PaymentGatewayService::class);
                    $result = $paymentGatewayService->processQRPayment($venta, $pago, $usuario);

                    return redirect()->route('pagos.show', $pago->id)->with([
                        'success' => 'Pago registrado y QR generado exitosamente. Escanea el código para pagar.',
                        'qr_generated' => true,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al generar QR en PagoController: ' . $e->getMessage());
                return redirect()->route('pagos.show', $pago->id)->with([
                    'success' => 'Pago registrado correctamente, pero hubo un error al generar el QR: ' . $e->getMessage(),
                    'warning' => true,
                ]);
            }
        }

        return redirect()->route('pagos.index')->with('success', 'Pago registrado correctamente');
    }

    public function show(Pago $pago)
    {
        return Inertia::render('Pagos/Show', [
            'pago' => $pago->load(['venta', 'metodoPago', 'usuario']),
        ]);
    }

    public function edit(Pago $pago)
    {
        // Solo mostrar métodos de pago: EFECTIVO y QR (PagoFácil)
        $metodosPago = MetodoPago::where('activo', true)
            ->where(function($q) {
                $q->where('es_electronico', false)
                  ->orWhere(function($q2) {
                      $q2->where('es_electronico', true)
                         ->where('tipo_electronico', 'QR');
                  });
            })
            ->get();

        return Inertia::render('Pagos/Edit', [
            'pago' => $pago->load(['venta', 'metodoPago', 'usuario']),
            'metodosPago' => $metodosPago,
        ]);
    }

    public function update(Request $request, Pago $pago)
    {
        $request->validate([
            'monto' => 'sometimes|required|numeric|min:0',
            'fecha_pago' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
            'estado' => 'sometimes|required|in:PENDIENTE,PAGADO,VENCIDO,CANCELADO',
            'tipo' => 'sometimes|required|in:CONTADO,CREDITO,CUOTA',
            'numero_cuota' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'metodo_pago_id' => 'sometimes|required|exists:metodo_pago,id',
        ]);

        // Si se marca como PAGADO y no tiene fecha_pago, asignarla
        if ($request->estado === 'PAGADO' && !$pago->fecha_pago) {
            $request->merge(['fecha_pago' => now()]);
        }

        $pago->update($request->all());
        return redirect()->route('pagos.index')->with('success', 'Pago actualizado correctamente');
    }

    public function registrarPago(Request $request, Pago $pago)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar pagos']);
        }

        if ($pago->estado === 'PAGADO') {
            return back()->withErrors(['error' => 'El pago ya está registrado']);
        }

        DB::beginTransaction();
        try {
            // Obtener el ID del usuario correctamente (no el email)
            $usuarioId = $request->user()?->id ?? Auth::user()?->id;

            $pago->update([
                'estado' => 'PAGADO',
                'fecha_pago' => now(),
                'usuario_id' => $usuarioId,
            ]);

            // Verificar si todas las cuotas están pagadas para confirmar la venta
            $venta = $pago->venta;
            if ($venta && !$venta->estado) {
                $pagosPendientes = $venta->pagos()->where('estado', '!=', 'PAGADO')->count();
                if ($pagosPendientes === 0) {
                    // Todas las cuotas pagadas, confirmar venta
                    $venta->estado = true;
                    $venta->save();

                    // Generar movimientos de inventario si aún no existen
                    foreach ($venta->detalles as $detalle) {
                        if ($detalle->producto_id && $detalle->estado === false) {
                            $producto = Producto::findOrFail($detalle->producto_id);

                            if ($producto->stock >= $detalle->cantidad) {
                                $producto->stock -= $detalle->cantidad;
                                $producto->save();

                                MovimientoInventario::create([
                                    'tipo' => 'SALIDA',
                                    'cantidad' => $detalle->cantidad,
                                    'motivo' => 'Venta a crédito - Todas las cuotas pagadas',
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
                    }
                }
            }

            DB::commit();
            return redirect()->route('pagos.index')->with('success', 'Pago registrado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
