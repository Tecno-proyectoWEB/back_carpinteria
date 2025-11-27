<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('pagos.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver pagos']);
        }

        $query = Pago::with(['pedido', 'metodoPago', 'usuario']);

        if ($request->has('pedido_id')) {
            $query->where('pedido_id', $request->pedido_id);
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
            'filters' => $request->only(['pedido_id', 'estado', 'tipo']),
        ]);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->tienePermiso('pagos.registrar')) {
            return back()->withErrors(['message' => 'No tiene permiso para registrar pagos']);
        }

        return Inertia::render('Pagos/Create', [
            'pedidos' => Pedido::where('estado', false)->with('usuario')->get(),
            'metodosPago' => MetodoPago::all(),
            'pedido_id' => $request->pedido_id,
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
            'pedido_id' => 'required|exists:pedido,id',
            'metodo_pago_id' => 'required|exists:metodo_pago,id',
        ]);

        $pago = Pago::create([
            'monto' => $request->monto,
            'fecha_pago' => $request->fecha_pago ?? ($request->estado === 'PAGADO' ? now() : null),
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'estado' => $request->estado,
            'tipo' => $request->tipo,
            'numero_cuota' => $request->numero_cuota,
            'observaciones' => $request->observaciones,
            'pedido_id' => $request->pedido_id,
            'metodo_pago_id' => $request->metodo_pago_id,
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('pagos.index')->with('success', 'Pago registrado correctamente');
    }

    public function show(Pago $pago)
    {
        return Inertia::render('Pagos/Show', [
            'pago' => $pago->load(['pedido', 'metodoPago', 'usuario']),
        ]);
    }

    public function edit(Pago $pago)
    {
        return Inertia::render('Pagos/Edit', [
            'pago' => $pago->load(['pedido', 'metodoPago', 'usuario']),
            'metodosPago' => MetodoPago::all(),
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
            $pago->update([
                'estado' => 'PAGADO',
                'fecha_pago' => now(),
                'usuario_id' => Auth::id(),
            ]);

            // Verificar si todas las cuotas están pagadas para confirmar el pedido
            $pedido = $pago->pedido;
            if ($pedido && !$pedido->estado) {
                $pagosPendientes = $pedido->pagos()->where('estado', '!=', 'PAGADO')->count();
                if ($pagosPendientes === 0) {
                    // Todas las cuotas pagadas, confirmar pedido
                    $pedido->estado = true;
                    $pedido->save();

                    // Generar movimientos de inventario si aún no existen
                    foreach ($pedido->detalles as $detalle) {
                        if ($detalle->producto_id && $detalle->estado === false) {
                            $producto = Producto::findOrFail($detalle->producto_id);

                            if ($producto->stock >= $detalle->cantidad) {
                                $producto->stock -= $detalle->cantidad;
                                $producto->save();

                                MovimientoInventario::create([
                                    'tipo' => 'SALIDA',
                                    'cantidad' => $detalle->cantidad,
                                    'motivo' => 'Venta a crédito - Todas las cuotas pagadas',
                                    'observaciones' => "Pedido #{$pedido->id} - {$producto->nombre}",
                                    'material_id' => null,
                                    'producto_id' => $producto->id,
                                    'pedido_id' => $pedido->id,
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
}
