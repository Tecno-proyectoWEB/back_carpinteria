<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PagoFacilController extends Controller
{
    public function crearCupon(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.crear')) {
            abort(403, 'No tiene permiso para crear cupones de pago');
        }

        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedido,id',
        ], [
            'pedido_id.required' => 'El ID del pedido es obligatorio.',
            'pedido_id.exists' => 'El pedido especificado no existe.',
        ]);

        $pedido = Pedido::findOrFail($validated['pedido_id']);

        // Simulación de creación de cupón Pagofacil
        // TODO: Integrar con SDK de Pagofacil
        $cuponId = 'PF-' . str_pad($pedido->id, 8, '0', STR_PAD_LEFT);
        $codigoBarras = '12345678901234567890';
        $qrCode = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($cuponId);

        \App\Models\Bitacora::create([
            'accion' => 'Cupón Pagofacil creado',
            'modulo' => 'PagoFacil',
            'tabla_afectada' => 'pedido',
            'registro_id' => $pedido->id,
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return Inertia::render('PagoFacil/Cupon', [
            'pedido' => $pedido->load(['usuario', 'detalles.producto', 'detalles.servicio']),
            'cupon' => [
                'id' => $cuponId,
                'codigo_barras' => $codigoBarras,
                'qr_code' => $qrCode,
                'monto' => $pedido->importe_total_desc,
                'vencimiento' => now()->addDays(7)->format('Y-m-d'),
                'instrucciones' => 'Puede pagar este cupón en cualquier local de Pagofacil o mediante transferencia.',
            ],
        ]);
    }

    public function verificarPago(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            abort(403, 'No tiene permiso para verificar pagos');
        }

        $validated = $request->validate([
            'cupon_id' => 'required|string',
        ], [
            'cupon_id.required' => 'El ID del cupón es obligatorio.',
        ]);

        // TODO: Integrar con API de Pagofacil para verificar estado
        $estado = 'PENDIENTE'; // PENDIENTE, PAGADO, VENCIDO

        return response()->json([
            'cupon_id' => $validated['cupon_id'],
            'estado' => $estado,
            'fecha_verificacion' => now()->toDateTimeString(),
        ]);
    }

    public function crearPlanPagos(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.crear')) {
            abort(403, 'No tiene permiso para crear planes de pago');
        }

        $validated = $request->validate([
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

        $pedido = Pedido::findOrFail($validated['pedido_id']);
        $numeroCuotas = $validated['numero_cuotas'];
        $montoTotal = $pedido->importe_total_desc;
        $montoCuota = $montoTotal / $numeroCuotas;
        $fechaPrimeraCuota = new \DateTime($validated['fecha_primera_cuota']);

        DB::beginTransaction();
        try {
            $pagos = [];
            for ($i = 1; $i <= $numeroCuotas; $i++) {
                $fechaVencimiento = clone $fechaPrimeraCuota;
                $fechaVencimiento->modify('+' . ($i - 1) . ' month');

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

            \App\Models\Bitacora::create([
                'accion' => 'Plan de pagos creado',
                'modulo' => 'PagoFacil',
                'tabla_afectada' => 'pago',
                'registro_id' => $pagos[0]->id,
                'usuario_id' => Auth::id(),
                'fecha' => now(),
            ]);

            DB::commit();

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', "Plan de pagos creado exitosamente con {$numeroCuotas} cuotas");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el plan de pagos: ' . $e->getMessage()]);
        }
    }

    public function showPlanPagos(Request $request, Pedido $pedido)
    {
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            abort(403, 'No tiene permiso para ver planes de pago');
        }

        $pedido->load(['usuario', 'detalles.producto', 'detalles.servicio', 'pagos']);

        return Inertia::render('PagoFacil/PlanPagos', [
            'pedido' => $pedido,
        ]);
    }
}

