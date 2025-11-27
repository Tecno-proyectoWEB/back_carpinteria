<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Pago;
use App\Models\Bitacora;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PagoFacilController extends Controller
{
    /**
     * Crear cupón de pago para Pagofacil
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearCupon(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedido,id',
        ], [
            'pedido_id.required' => 'El ID del pedido es obligatorio.',
            'pedido_id.exists' => 'El pedido especificado no existe.',
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);

        // Aquí se integraría con la API de Pagofacil
        // Por ahora, simulamos la creación del cupón
        
        // TODO: Integrar con SDK de Pagofacil
        // $pagofacil = new \App\Services\PagoFacil\PagoFacilService();
        // $cupon = $pagofacil->crearCupon([
        //     'monto' => $pedido->importe_total_desc,
        //     'concepto' => "Pedido #{$pedido->id} - Carpintería Jorge",
        //     'vencimiento' => now()->addDays(7)->format('Y-m-d'),
        // ]);

        // Simulación de respuesta de Pagofacil
        $cuponId = 'PF-' . str_pad($pedido->id, 8, '0', STR_PAD_LEFT);
        $codigoBarras = '12345678901234567890';
        $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($cuponId);

        // Guardar información del cupón en la base de datos
        // (Se puede crear una tabla pagofacil_payments similar a stripe_payments)
        
        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Cupón Pagofacil creado',
            'modulo' => 'PagoFacil',
            'tabla_afectada' => 'pedido',
            'registro_id' => $pedido->id,
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $response = [
            'success' => true,
            'cupon' => [
                'id' => $cuponId,
                'codigo_barras' => $codigoBarras,
                'qr_code' => $qrCode,
                'monto' => $pedido->importe_total_desc,
                'vencimiento' => now()->addDays(7)->format('Y-m-d'),
                'instrucciones' => 'Puede pagar este cupón en cualquier local de Pagofacil o mediante transferencia.',
            ],
        ];

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($response);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('PagoFacil/Cupon', [
            'cupon' => $response['cupon'],
            'pedido' => $pedido->load(['usuario', 'metodoPago']),
        ]);
    }

    public function showPlanPagos(Request $request, Pedido $pedido)
    {
        // Solo para web
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use POST /api/pagofacil/crear-plan-pagos para crear plan'], 405);
        }

        $pedido->load(['usuario', 'metodoPago', 'pagos']);

        return Inertia::render('PagoFacil/PlanPagos', [
            'pedido' => $pedido,
        ]);
    }

    /**
     * Verificar estado de pago en Pagofacil
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarPago(Request $request)
    {
        $request->validate([
            'cupon_id' => 'required|string',
        ], [
            'cupon_id.required' => 'El ID del cupón es obligatorio.',
        ]);

        // TODO: Integrar con API de Pagofacil para verificar estado
        // $pagofacil = new \App\Services\PagoFacil\PagoFacilService();
        // $estado = $pagofacil->verificarPago($request->cupon_id);

        // Simulación
        $estado = 'PENDIENTE'; // PENDIENTE, PAGADO, VENCIDO

        return response()->json([
            'cupon_id' => $request->cupon_id,
            'estado' => $estado,
            'fecha_verificacion' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Webhook de Pagofacil para notificaciones de pago
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        // TODO: Validar firma del webhook de Pagofacil
        // TODO: Procesar notificación de pago
        
        $data = $request->all();

        // Ejemplo de procesamiento
        if (isset($data['cupon_id']) && isset($data['estado']) && $data['estado'] === 'PAGADO') {
            // Buscar el pedido relacionado
            // Actualizar estado del pedido
            // Crear registro de pago
            // Actualizar stock si es necesario
        }

        return response()->json(['success' => true]);
    }

    /**
     * Crear plan de pagos para un pedido
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearPlanPagos(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedido,id',
            'numero_cuotas' => 'required|integer|min:2|max:12',
            'fecha_primera_cuota' => 'required|date|after:today',
        ], [
            'pedido_id.required' => 'El ID del pedido es obligatorio.',
            'pedido_id.exists' => 'El pedido especificado no existe.',
            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'numero_cuotas.min' => 'El número mínimo de cuotas es 2.',
            'numero_cuotas.max' => 'El número máximo de cuotas es 12.',
            'fecha_primera_cuota.required' => 'La fecha de la primera cuota es obligatoria.',
            'fecha_primera_cuota.date' => 'La fecha de la primera cuota debe ser una fecha válida.',
            'fecha_primera_cuota.after' => 'La fecha de la primera cuota debe ser posterior a hoy.',
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);
        $numeroCuotas = $request->numero_cuotas;
        $montoTotal = $pedido->importe_total_desc;
        $montoCuota = $montoTotal / $numeroCuotas;
        $fechaPrimeraCuota = new \DateTime($request->fecha_primera_cuota);

        DB::beginTransaction();
        try {
            $pagos = [];
            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $fechaVencimiento = clone $fechaPrimeraCuota;
                $fechaVencimiento->modify('+' . ($i - 1) . ' month');

                // Ajustar última cuota si hay diferencia por redondeo
                $monto = ($i === $numeroCuotas) 
                    ? $montoTotal - ($montoCuota * ($numeroCuotas - 1))
                    : $montoCuota;

                $pago = Pago::create([
                    'pedido_id' => $pedido->id,
                    'monto' => $monto,
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'estado' => 'PENDIENTE',
                    'tipo' => 'CUOTA',
                    'numero_cuota' => $i,
                    'metodo_pago_id' => $pedido->metodo_pago_id,
                ]);

                $pagos[] = $pago;
            }

            // Registrar en bitácora
            Bitacora::create([
                'accion' => 'Plan de pagos creado',
                'modulo' => 'PagoFacil',
                'tabla_afectada' => 'pago',
                'registro_id' => $pagos[0]->id,
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            DB::commit();

            $response = [
                'success' => true,
                'message' => 'Plan de pagos creado exitosamente',
                'plan' => [
                    'pedido_id' => $pedido->id,
                    'numero_cuotas' => $numeroCuotas,
                    'monto_total' => $montoTotal,
                    'monto_cuota' => round($montoCuota, 2),
                    'fecha_primera_cuota' => $fechaPrimeraCuota->format('Y-m-d'),
                    'pagos' => $pagos,
                ],
            ];

            // Si es petición API, retornar JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json($response, 201);
            }

            // Si es petición web, redirigir
            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', 'Plan de pagos creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el plan de pagos: ' . $e->getMessage(),
                ], 500);
            }
            return back()->withErrors(['error' => 'Error al crear el plan de pagos: ' . $e->getMessage()]);
        }
    }
}

