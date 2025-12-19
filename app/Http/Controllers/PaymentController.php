<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Bitacora;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

date_default_timezone_set('America/La_Paz');

class PaymentController extends Controller
{
    /**
     * Callback desde PagoFacil
     * PagoFácil envía un POST a esta URL cuando el usuario paga
     */
    public function callback(Request $request)
    {
        try {
            Log::info('Callback recibido de PagoFacil', [
                'data' => $request->all(),
                'headers' => $request->headers->all(),
                'method' => $request->method()
            ]);

            $ventaID = $request->input("VentaID")
                     ?? $request->input("PedidoID") // Mantener compatibilidad con callbacks antiguos
                     ?? $request->input("paymentNumber")
                     ?? $request->input("payment_number")
                     ?? $request->input("nro_pago");

            $estado = $request->input("Estado")
                   ?? $request->input("status")
                   ?? $request->input("estado");

            $fecha = $request->input("Fecha");
            $hora = $request->input("Hora");
            $metodoPago = $request->input("MetodoPago");

            Log::info('Datos extraídos del callback', [
                'VentaID' => $ventaID,
                'Estado' => $estado,
                'Fecha' => $fecha,
                'Hora' => $hora,
                'MetodoPago' => $metodoPago
            ]);

            if (!$ventaID) {
                Log::warning('Callback sin VentaID/PedidoID', ['request' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "VentaID/PedidoID no encontrado en el callback",
                    'values' => false
                ], 400);
            }

            $estadoAprobado = in_array(strtoupper($estado ?? ''), [
                'APROBADO', 'APPROVED', 'COMPLETED', 'PAID', 'SUCCESS',
                '1', 1, true, 'PAGADO'
            ]);

            if ($estadoAprobado) {
                $paymentGatewayService = app(PaymentGatewayService::class);

                $pago = Pago::where('nro_pago', $ventaID)->first();
                $datosAnteriores = $pago ? $pago->toArray() : null;

                if ($pago) {
                    if ($fecha && $hora) {
                        try {
                            $pago->fecha_confirmacion = \Carbon\Carbon::parse($fecha . ' ' . $hora);
                        } catch (\Exception $e) {
                            Log::warning('Error parseando fecha del callback', [
                                'fecha' => $fecha,
                                'hora' => $hora
                            ]);
                        }
                    }

                    if ($metodoPago) {
                        $pago->metodo_pago_facil = $metodoPago;
                        Log::info('Método de pago recibido en callback', ['metodo' => $metodoPago]);
                    }

                    $pago->save();
                }

                // Confirmar el pago
                $paymentGatewayService->confirmPayment($ventaID);

                // Registrar en bitácora
                if ($pago) {
                    Bitacora::create([
                        'tipo_accion_id' => 2, // ACTUALIZAR
                        'tabla_afectada' => 'pago',
                        'registro_id' => $pago->id,
                        'usuario_id' => $pago->usuario_id,
                        'datos_anteriores' => $datosAnteriores,
                        'datos_nuevos' => $pago->fresh()->toArray(),
                        'descripcion' => "Pago confirmado vía callback PagoFácil - VentaID: {$ventaID}",
                        'ip' => $request->ip(),
                    ]);
                }

                Log::info('Pago confirmado desde callback', [
                    'VentaID' => $ventaID,
                    'Estado' => $estado,
                    'Fecha' => $fecha,
                    'Hora' => $hora
                ]);
            } else {
                Log::info('Callback recibido pero estado no es APROBADO', [
                    'VentaID' => $ventaID,
                    'Estado' => $estado
                ]);
            }

            // IMPORTANTE: Responder con formato exacto según documentación
            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Notificación recibida',
                'values' => true
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error en callback de pago: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => "Error al procesar el callback: " . $th->getMessage(),
                'values' => false
            ], 200); // Responder 200 para evitar reenvíos
        }
    }

    /**
     * Consultar estado de pago
     */
    public function checkStatus($id)
    {
        $pago = Pago::with(['venta.usuario', 'venta.detalles.producto', 'venta.detalles.servicio'])
                    ->findOrFail($id);

        $paymentGatewayService = app(PaymentGatewayService::class);
        $result = $paymentGatewayService->consultPaymentStatus($pago);

        $pago->refresh();

        $paymentInfo = null;
        if (is_array($result) && isset($result['paymentInfo'])) {
            $paymentInfo = $result['paymentInfo'];
        }

        return \Inertia\Inertia::render('Pagos/Status', [
            'pago' => $pago->fresh([
                'venta.usuario',
                'venta.detalles.producto',
                'venta.detalles.servicio'
            ]),
            'paymentInfo' => $paymentInfo
        ]);
    }
}

