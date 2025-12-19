<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\MetodoPago;
use App\Models\Bitacora;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Corrección 5.10: Filtrar métodos de pago para mostrar solo EFECTIVO y QR
        return Inertia::render('Pagos/Create', [
            'ventas' => Venta::where('estado', false)->with('usuario')->get(),
            'metodosPago' => MetodoPago::where('activo', true)
                ->where(function($q) {
                    $q->where('nombre', 'EFECTIVO')
                      ->orWhere(function($q2) {
                          $q2->where('es_electronico', true)
                             ->where('tipo_electronico', 'QR');
                      });
                })->get(),
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

        // Corrección 5.1: Usar user ID numérico correcto
        $usuarioId = $request->user()?->id ?? Auth::user()?->id;

        $metodoPago = MetodoPago::find($request->metodo_pago_id);
        $venta = Venta::find($request->venta_id);
        $usuario = $venta->usuario;

        $pago = Pago::create([
            'monto' => $request->monto,
            'fecha_pago' => ($metodoPago && $metodoPago->es_electronico) ? null : ($request->estado === 'PAGADO' ? now() : null),
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => ($metodoPago && $metodoPago->es_electronico) ? 'PENDIENTE' : $request->estado,
            'tipo' => $request->tipo,
            'numero_cuota' => $request->numero_cuota,
            'observaciones' => $request->observaciones,
            'venta_id' => $request->venta_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'usuario_id' => $usuarioId,
        ]);

        // Corrección 5.10: Generar QR automáticamente si el método es QR
        if ($metodoPago && $metodoPago->es_electronico && $metodoPago->tipo_electronico === 'QR') {
            try {
                $paymentGatewayService = app(PaymentGatewayService::class);
                $result = $paymentGatewayService->processQRPayment($venta, $pago, $usuario);
                $pago->refresh();
                
                return redirect()->route('pagos.show', $pago->id)->with([
                    'success' => 'Pago registrado. Escanea el código QR para pagar.',
                    'qr_generated' => true
                ]);
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
        $pago->load(['venta', 'metodoPago', 'usuario']);
        
        // Si el pago tiene QR y está pendiente, pasar información para el componente
        $pagoConQR = null;
        if ($pago->qr_image && $pago->estado === 'PENDIENTE') {
            $pagoConQR = $pago;
        }

        return Inertia::render('Pagos/Show', [
            'pago' => $pago,
            'pagoConQR' => $pagoConQR,
        ]);
    }

    public function edit(Pago $pago)
    {
        return Inertia::render('Pagos/Edit', [
            'pago' => $pago->load(['venta', 'metodoPago', 'usuario']),
            'metodosPago' => MetodoPago::where('activo', true)
                ->where(function($q) {
                    $q->where('nombre', 'EFECTIVO')
                      ->orWhere(function($q2) {
                          $q2->where('es_electronico', true)
                             ->where('tipo_electronico', 'QR');
                      });
                })->get(),
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

    public function registrarPago(Pago $pago)
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
            $datosAnteriores = $pago->toArray();

            $pago->update([
                'estado' => 'PAGADO',
                'fecha_pago' => now(),
                'usuario_id' => Auth::id(),
            ]);

            // Registrar en bitácora
            Bitacora::create([
                'tipo_accion_id' => 2, // ACTUALIZAR
                'tabla_afectada' => 'pago',
                'registro_id' => $pago->id,
                'usuario_id' => Auth::id(),
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => $pago->toArray(),
                'descripcion' => "Pago #{$pago->id} registrado como PAGADO - Venta #{$pago->venta_id}",
                'ip' => request()->ip(),
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
                                    'usuario_id' => Auth::id(),
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

    /**
     * Crea un plan de pagos en cuotas para una venta
     * @param Request $request Debe contener: venta_id, numero_cuotas (2-12), fecha_primera_cuota
     * @return \Illuminate\Http\RedirectResponse
     */
    public function crearPlanPagos(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear planes de pago']);
        }

        $request->validate([
            'venta_id' => 'required|exists:venta,id',
            'numero_cuotas' => 'required|integer|min:2|max:12',
            'fecha_primera_cuota' => 'required|date|after_or_equal:today',
            'metodo_pago_id' => 'nullable|exists:metodo_pago,id',
        ]);

        DB::beginTransaction();
        try {
            $venta = Venta::findOrFail($request->venta_id);

            // Verificar que la venta no tenga pagos existentes
            if ($venta->pagos()->count() > 0) {
                return back()->withErrors(['error' => 'La venta ya tiene pagos registrados']);
            }

            $numeroCuotas = $request->numero_cuotas;
            $montoTotal = $venta->total;
            $montoPorCuota = floor(($montoTotal / $numeroCuotas) * 100) / 100; // Redondear hacia abajo
            $montoUltimaCuota = $montoTotal - ($montoPorCuota * ($numeroCuotas - 1)); // Ajustar última cuota

            $fechaPrimeraCuota = Carbon::parse($request->fecha_primera_cuota);

            // Determinar método de pago (por defecto EFECTIVO)
            $metodoPagoId = $request->metodo_pago_id;
            if (!$metodoPagoId) {
                $metodoPago = MetodoPago::where('nombre', 'EFECTIVO')->where('activo', true)->first();
                $metodoPagoId = $metodoPago ? $metodoPago->id : null;
            }

            $pagosCreados = [];

            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $fechaVencimiento = $fechaPrimeraCuota->copy()->addMonths($i - 1);
                $monto = ($i === $numeroCuotas) ? $montoUltimaCuota : $montoPorCuota;

                $pago = Pago::create([
                    'monto' => $monto,
                    'fecha_pago' => null,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 'PENDIENTE',
                    'tipo' => 'CUOTA',
                    'numero_cuota' => $i,
                    'observaciones' => "Cuota {$i} de {$numeroCuotas} - Plan de pagos",
                    'venta_id' => $venta->id,
                    'metodo_pago_id' => $metodoPagoId,
                    'usuario_id' => Auth::id(),
                ]);

                $pagosCreados[] = $pago;
            }

            // Registrar en bitácora
            Bitacora::create([
                'tipo_accion_id' => 1, // CREAR
                'tabla_afectada' => 'pago',
                'registro_id' => $venta->id,
                'usuario_id' => Auth::id(),
                'datos_anteriores' => null,
                'datos_nuevos' => [
                    'venta_id' => $venta->id,
                    'numero_cuotas' => $numeroCuotas,
                    'monto_total' => $montoTotal,
                    'monto_por_cuota' => $montoPorCuota,
                    'pagos_creados' => count($pagosCreados),
                    'fecha_primera_cuota' => $fechaPrimeraCuota->toDateString(),
                ],
                'descripcion' => "Plan de pagos creado: {$numeroCuotas} cuotas para venta #{$venta->id}",
                'ip' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('pagos.index', ['venta_id' => $venta->id])
                ->with('success', "Plan de pagos creado: {$numeroCuotas} cuotas");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear plan de pagos: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el plan de pagos: ' . $e->getMessage()]);
        }
    }
}
