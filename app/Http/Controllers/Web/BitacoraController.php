<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('bitacora.ver')) {
            abort(403, 'No tiene permiso para ver la bitácora');
        }

        $query = Bitacora::with(['usuario', 'tipoAccion']);

        // Filtros
        if ($request->has('modulo') && $request->modulo) {
            $query->where('modulo', $request->modulo);
        }

        if ($request->has('accion') && $request->accion) {
            $query->where('accion', 'ILIKE', "%{$request->accion}%");
        }

        if ($request->has('usuario_id') && $request->usuario_id) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->has('tabla_afectada') && $request->tabla_afectada) {
            $query->where('tabla_afectada', 'ILIKE', "%{$request->tabla_afectada}%");
        }

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->where('fecha', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->where('fecha', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        // Búsqueda general
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('accion', 'ILIKE', "%{$search}%")
                  ->orWhere('modulo', 'ILIKE', "%{$search}%")
                  ->orWhere('tabla_afectada', 'ILIKE', "%{$search}%")
                  ->orWhereHas('usuario', function($q) use ($search) {
                      $q->where('nombre', 'ILIKE', "%{$search}%")
                        ->orWhere('apellido', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'fecha');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $bitacoras = $query->paginate($request->get('per_page', 20));

        // Obtener módulos únicos para el filtro
        $modulos = Bitacora::select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        // Obtener usuarios para el filtro
        $usuarios = \App\Models\Usuario::where('estado', true)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Bitacora/Index', [
            'bitacoras' => $bitacoras,
            'modulos' => $modulos,
            'usuarios' => $usuarios,
            'filters' => $request->only(['modulo', 'accion', 'usuario_id', 'tabla_afectada', 'fecha_desde', 'fecha_hasta', 'search', 'sort_by', 'sort_dir']),
        ]);
    }

    public function show(Bitacora $bitacora)
    {
        if (!Auth::user()->tienePermiso('bitacora.ver')) {
            abort(403, 'No tiene permiso para ver la bitácora');
        }

        $bitacora->load(['usuario', 'tipoAccion']);

        return Inertia::render('Bitacora/Show', [
            'bitacora' => $bitacora,
        ]);
    }
}

