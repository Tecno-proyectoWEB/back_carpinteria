<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BitacoraController extends BaseController
{
    public function index(Request $request)
    {
        // Verificar permisos (ajustar según tu sistema de permisos)
        if (!Auth::user()->tienePermiso('bitacora.ver')) {
            return $this->respondError('No tiene permiso para ver la bitácora', 403);
        }

        $query = Bitacora::with(['usuario', 'tipoAccion']);

        // Filtros
        if ($request->has('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Si es petición API, retornar JSON
        if ($this->isApiRequest($request)) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $bitacoras = $query->paginate($request->get('per_page', 15));

        return $this->respond($bitacoras, 'Bitacora/Index', [
            'bitacoras' => $bitacoras,
            'filters' => $request->only(['modulo', 'usuario_id', 'fecha_desde', 'fecha_hasta', 'sort_by', 'sort_dir']),
        ]);
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

    public function show(Request $request, Bitacora $bitacora)
    {
        $bitacora->load(['usuario', 'tipoAccion']);

        return $this->respond($bitacora, 'Bitacora/Show', [
            'bitacora' => $bitacora,
        ]);
    }
}
