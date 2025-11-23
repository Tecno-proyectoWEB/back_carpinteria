<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\StripePayment;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\DB;

class StripePaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedido,id',
            'payment_method_id' => 'required|string',
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);

        // Configura la clave secreta de Stripe (agrega en .env STRIPE_SECRET)
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Crea un PaymentIntent con el importe total del pedido
            $paymentIntent = PaymentIntent::create([
                'amount' => intval($pedido->importe_total * 100), // en centavos
                'currency' => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirmation_method' => 'manual',
                'confirm' => true,
            ]);

            // Guardar pago parcialmente, pendiente confirmación
            $stripePayment = StripePayment::create([
                'pedido_id' => $pedido->id,
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $pedido->importe_total,
            ]);

            // Registro en bitacora
            Bitacora::create([
                'accion' => 'Creación PaymentIntent',
                'modulo' => 'StripePayment',
                'tabla_afectada' => 'stripe_payments',
                'registro_id' => $stripePayment->id,
                'usuario_id' => auth()->id(),
            ]);

            return response()->json($paymentIntent);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);
            $paymentIntent->confirm();

            $stripePayment = StripePayment::where('payment_intent_id', $request->payment_intent_id)->firstOrFail();
            $stripePayment->status = $paymentIntent->status;
            $stripePayment->save();

            $pedido = Pedido::findOrFail($stripePayment->pedido_id);

            if ($paymentIntent->status === 'succeeded') {
                // Actualizar estado del pedido
                $pedido->estado = true;
                $pedido->save();

                // Aquí lógica para actualizar stock de los productos del pedido...

                // Registro en bitacora
            Bitacora::create([
                'accion' => 'Pago confirmado',
                'modulo' => 'StripePayment',
                'tabla_afectada' => 'pedidos',
                'registro_id' => $pedido->id,
                'usuario_id' => auth()->id(),
            ]);

            // Actualizar stock de productos en el pedido
            foreach ($pedido->detalles as $detalle) {
                $producto = $detalle->producto;
                if ($producto) {
                    $producto->stock = max(0, $producto->stock - $detalle->cantidad);
                    $producto->save();
                }
            }

                return response()->json(['message' => 'Pago exitoso y pedido actualizado']);

            } else {
                return response()->json(['message' => 'Estado pago: ' . $paymentIntent->status]);
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
