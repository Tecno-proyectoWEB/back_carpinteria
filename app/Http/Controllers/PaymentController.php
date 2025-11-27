<?php

namespace App\Http\Controllers;

use App\Models\Pago;
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

            $ventaID = $request->input("PedidoID")
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
                'PedidoID' => $ventaID,
                'Estado' => $estado,
                'Fecha' => $fecha,
                'Hora' => $hora,
                'MetodoPago' => $metodoPago
            ]);

            if (!$ventaID) {
                Log::warning('Callback sin PedidoID', ['request' => $request->all()]);
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => "PedidoID no encontrado en el callback",
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

                Log::info('Pago confirmado desde callback', [
                    'PedidoID' => $ventaID,
                    'Estado' => $estado,
                    'Fecha' => $fecha,
                    'Hora' => $hora
                ]);
            } else {
                Log::info('Callback recibido pero estado no es APROBADO', [
                    'PedidoID' => $ventaID,
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

