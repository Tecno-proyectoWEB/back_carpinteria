<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MetodoPagoController extends Controller
{
    public function index()
    {
        if (!Auth::user()->tienePermiso('metodos_pago.ver')) {
            abort(403, 'No tiene permiso para ver métodos de pago');
        }

        $metodosPago = MetodoPago::withCount(['pedidos', 'pagos'])->orderBy('nombre')->get();

        return Inertia::render('MetodosPago/Index', [
            'metodosPago' => $metodosPago,
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('metodos_pago.crear')) {
            abort(403, 'No tiene permiso para crear métodos de pago');
        }

        return Inertia::render('MetodosPago/Create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('metodos_pago.crear')) {
            abort(403, 'No tiene permiso para crear métodos de pago');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:metodo_pago,nombre',
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este método de pago ya existe.',
        ]);

        $metodoPago = MetodoPago::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Método de pago creado',
            'modulo' => 'MétodoPago',
            'tabla_afectada' => 'metodo_pago',
            'registro_id' => $metodoPago->id,
            'datos_nuevos' => $metodoPago->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago creado exitosamente');
    }

    public function edit(MetodoPago $metodoPago)
    {
        if (!Auth::user()->tienePermiso('metodos_pago.editar')) {
            abort(403, 'No tiene permiso para editar métodos de pago');
        }

        return Inertia::render('MetodosPago/Edit', [
            'metodoPago' => $metodoPago,
        ]);
    }

    public function update(Request $request, MetodoPago $metodoPago)
    {
        if (!Auth::user()->tienePermiso('metodos_pago.editar')) {
            abort(403, 'No tiene permiso para editar métodos de pago');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:metodo_pago,nombre,' . $metodoPago->id,
            'descripcion' => 'nullable|string',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => 'Este método de pago ya existe.',
        ]);

        $datosAnteriores = $metodoPago->toArray();
        $metodoPago->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Método de pago actualizado',
            'modulo' => 'MétodoPago',
            'tabla_afectada' => 'metodo_pago',
            'registro_id' => $metodoPago->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $metodoPago->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago actualizado exitosamente');
    }

    public function destroy(MetodoPago $metodoPago)
    {
        if (!Auth::user()->tienePermiso('metodos_pago.eliminar')) {
            abort(403, 'No tiene permiso para eliminar métodos de pago');
        }

        // Verificar si tiene pedidos o pagos asociados
        if ($metodoPago->pedidos()->count() > 0 || $metodoPago->pagos()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar un método de pago que tiene pedidos o pagos asociados']);
        }

        \App\Models\Bitacora::create([
            'accion' => 'Método de pago eliminado',
            'modulo' => 'MétodoPago',
            'tabla_afectada' => 'metodo_pago',
            'registro_id' => $metodoPago->id,
            'datos_anteriores' => $metodoPago->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $metodoPago->delete();

        return redirect()->route('metodos-pago.index')
            ->with('success', 'Método de pago eliminado exitosamente');
    }
}

