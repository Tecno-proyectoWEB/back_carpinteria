<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Bitacora;
use Illuminate\Http\Request;

class PedidoPagoController extends Controller
{
    public function pagoTransferencia(Request $request, Pedido $pedido)
    {
        $request->validate([
            'referencia_transferencia' => 'required|string|max:255',
            'monto_pagado' => 'required|numeric|min:0',
        ]);

        if ($pedido->estado == 'pagado') {
            return response()->json(['message' => 'El pedido ya ha sido pagado'], 400);
        }

        // Aquí se puede añadir la lógica para verificar la transferencia externamente si se desea

        $pedido->update([
            'estado' => 'pagado',
            'metodo_pago' => 'transferencia',
            'referencia_pago' => $request->referencia_transferencia,
            'monto_pagado' => $request->monto_pagado,
        ]);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Pago por transferencia realizado',
            'modulo' => 'Pedido',
            'tabla_afectada' => 'pedido',
            'registro_id' => $pedido->id,
            'usuario_id' => auth()->id(),
            'datos_nuevos' => $pedido->toArray(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Pago por transferencia registrado con éxito', 'pedido' => $pedido]);
    }

    public function pagoQR(Request $request, Pedido $pedido)
    {
        $request->validate([
            'codigo_qr' => 'required|string',
            'monto_pagado' => 'required|numeric|min:0',
        ]);

        if ($pedido->estado == 'pagado') {
            return response()->json(['message' => 'El pedido ya ha sido pagado'], 400);
        }

        // Aquí puede añadirse lógica para la verificación del QR

        $pedido->update([
            'estado' => 'pagado',
            'metodo_pago' => 'qr',
            'referencia_pago' => $request->codigo_qr,
            'monto_pagado' => $request->monto_pagado,
        ]);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Pago por QR realizado',
            'modulo' => 'Pedido',
            'tabla_afectada' => 'pedido',
            'registro_id' => $pedido->id,
            'usuario_id' => auth()->id(),
            'datos_nuevos' => $pedido->toArray(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->header('User-Agent'),
        ]);

        return response()->json(['message' => 'Pago por QR registrado con éxito', 'pedido' => $pedido]);
    }
}
