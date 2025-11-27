<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
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
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($query->get());
        }

        // Si es petición web, retornar Inertia con paginación
        $bitacoras = $query->paginate($request->get('per_page', 15));

        // Obtener menú y visitas para web
        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Bitacora/Index', [
            'bitacoras' => $bitacoras,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
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

        // Si es petición API, retornar JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($bitacora);
        }

        // Si es petición web, retornar Inertia
        return Inertia::render('Bitacora/Show', [
            'bitacora' => $bitacora,
        ]);
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
