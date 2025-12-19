<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlmacenController extends BaseController
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('almacenes.ver')) {
            return $this->respondError('No tiene permiso para ver almacenes', 403);
        }

        $almacenes = Almacen::orderBy('nombre')->get();

        if ($this->isApiRequest($request)) {
            return response()->json($almacenes);
        }

        return $this->respond($almacenes, 'Almacenes/Index', [
            'almacenes' => $almacenes,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('almacenes.crear')) {
            return $this->respondError('No tiene permiso para crear almacenes', 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'capacidad' => 'required|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $almacen = Almacen::create($validated);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Almacén creado',
            'modulo' => 'Almacenes',
            'tabla_afectada' => 'almacen',
            'registro_id' => $almacen->id,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json($almacen, 201);
        }

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén creado exitosamente');
    }

    public function show(Request $request, Almacen $almacen)
    {
        if (!Auth::user()->tienePermiso('almacenes.ver')) {
            return $this->respondError('No tiene permiso para ver almacenes', 403);
        }

        return $this->respond($almacen, 'Almacenes/Show', [
            'almacen' => $almacen,
        ]);
    }

    public function update(Request $request, Almacen $almacen)
    {
        if (!Auth::user()->tienePermiso('almacenes.editar')) {
            return $this->respondError('No tiene permiso para editar almacenes', 403);
        }

        $datosAnteriores = $almacen->toArray();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'capacidad' => 'required|numeric|min:0',
            'ubicacion' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

        $almacen->update($validated);

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Almacén actualizado',
            'modulo' => 'Almacenes',
            'tabla_afectada' => 'almacen',
            'registro_id' => $almacen->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json($almacen);
        }

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén actualizado exitosamente');
    }

    public function destroy(Request $request, Almacen $almacen)
    {
        if (!Auth::user()->tienePermiso('almacenes.eliminar')) {
            return $this->respondError('No tiene permiso para eliminar almacenes', 403);
        }

        $datosAnteriores = $almacen->toArray();

        $almacen->delete();

        // Registrar en bitácora
        Bitacora::create([
            'accion' => 'Almacén eliminado',
            'modulo' => 'Almacenes',
            'tabla_afectada' => 'almacen',
            'registro_id' => $almacen->id,
            'datos_anteriores' => $datosAnteriores,
            'usuario_id' => Auth::id(),
            'direccion_ip' => $request->ip(),
            'navegador' => $request->userAgent(),
            'fecha' => now(),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json(null, 204);
        }

        return redirect()->route('almacenes.index')
            ->with('success', 'Almacén eliminado exitosamente');
    }
}
