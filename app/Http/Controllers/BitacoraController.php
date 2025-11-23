<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index()
    {
        return response()->json(Bitacora::with(['usuario', 'tipoAccion'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'accion' => 'required|string|max:50',
            'modulo' => 'required|string|max:50',
            'tabla_afectada' => 'nullable|string|max:100',
            'registro_id' => 'nullable|integer',
            'datos_anteriores' => 'nullable|array',
            'datos_nuevos' => 'nullable|array',
            'usuario_id' => 'required|exists:usuario,id',
            'direccion_ip' => 'nullable|string|max:45',
            'navegador' => 'nullable|string',
            'tipo_accion_id' => 'nullable|exists:tipo_accion,id',
        ]);

        $bitacora = Bitacora::create($request->all());
        return response()->json($bitacora->load(['usuario', 'tipoAccion']), 201);
    }

    public function show(Bitacora $bitacora)
    {
        return response()->json($bitacora->load(['usuario', 'tipoAccion']));
    }

    public function update(Request $request, Bitacora $bitacora)
    {
        $request->validate([
            'accion' => 'sometimes|required|string|max:50',
            'modulo' => 'sometimes|required|string|max:50',
            'tabla_afectada' => 'nullable|string|max:100',
            'registro_id' => 'nullable|integer',
            'datos_anteriores' => 'nullable|array',
            'datos_nuevos' => 'nullable|array',
            'usuario_id' => 'sometimes|required|exists:usuario,id',
            'direccion_ip' => 'nullable|string|max:45',
            'navegador' => 'nullable|string',
            'tipo_accion_id' => 'nullable|exists:tipo_accion,id',
        ]);

        $bitacora->update($request->all());
        return response()->json($bitacora->load(['usuario', 'tipoAccion']));
    }

    public function destroy(Bitacora $bitacora)
    {
        $bitacora->delete();
        return response()->json(null, 204);
    }
}
