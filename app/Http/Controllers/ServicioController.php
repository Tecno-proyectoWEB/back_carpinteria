<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function index()
    {
        return response()->json(Servicio::with('categoria')->where('activo', true)->get());
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.crear')) {
            return response()->json(['message' => 'No tiene permiso para crear servicios'], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'categoria_id' => 'nullable|exists:categoria,id',
            'activo' => 'boolean',
        ]);

        $servicio = Servicio::create($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio creado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($servicio->load('categoria'), 201);
    }

    public function show(Servicio $servicio)
    {
        return response()->json($servicio->load('categoria'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.editar')) {
            return response()->json(['message' => 'No tiene permiso para editar servicios'], 403);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_base' => 'sometimes|required|numeric|min:0',
            'tiempo_estimado' => 'nullable|integer|min:0',
            'categoria_id' => 'nullable|exists:categoria,id',
            'activo' => 'boolean',
        ]);

        $datos_anteriores = $servicio->toArray();
        $servicio->update($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio actualizado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($servicio->load('categoria'));
    }

    public function destroy(Servicio $servicio)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('servicios.eliminar')) {
            return response()->json(['message' => 'No tiene permiso para eliminar servicios'], 403);
        }

        $datos_anteriores = $servicio->toArray();
        $servicio->update(['activo' => false]);

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Servicio desactivado',
            'modulo' => 'Servicio',
            'tabla_afectada' => 'servicio',
            'registro_id' => $servicio->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $servicio->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json(['message' => 'Servicio desactivado'], 200);
    }
}
