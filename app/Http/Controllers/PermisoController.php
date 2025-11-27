<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Http\Controllers\MenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->tienePermiso('permisos.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver permisos'], 403);
            }
            abort(403, 'No tiene permiso para ver permisos');
        }

        $permisos = Permiso::with('roles')->orderBy('nombre')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($permisos);
        }

        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Permisos/Index', [
            'permisos' => $permisos,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function create(Request $request)
    {
        if (!Auth::user()->tienePermiso('permisos.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear permisos'], 403);
            }
            abort(403, 'No tiene permiso para crear permisos');
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Use POST /permisos para crear']);
        }

        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Permisos/Create', [
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('permisos.crear')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para crear permisos'], 403);
            }
            abort(403, 'No tiene permiso para crear permisos');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:permiso,nombre',
        ]);

        $permiso = Permiso::create($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Permiso creado',
            'modulo' => 'Permiso',
            'tabla_afectada' => 'permiso',
            'registro_id' => $permiso->id,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($permiso, 201);
        }

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso creado exitosamente');
    }

    public function show(Request $request, Permiso $permiso)
    {
        if (!Auth::user()->tienePermiso('permisos.ver')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para ver permisos'], 403);
            }
            abort(403, 'No tiene permiso para ver permisos');
        }

        $permiso->load('roles');

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($permiso);
        }

        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Permisos/Show', [
            'permiso' => $permiso,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function edit(Request $request, Permiso $permiso)
    {
        if (!Auth::user()->tienePermiso('permisos.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar permisos'], 403);
            }
            abort(403, 'No tiene permiso para editar permisos');
        }

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($permiso);
        }

        $menuController = new MenuController();
        $menuItems = $menuController->getMenuForUser($request->user());
        $pageVisits = \App\Models\PageVisit::obtenerContador($request->path());

        return Inertia::render('Permisos/Edit', [
            'permiso' => $permiso,
            'menuItems' => $menuItems,
            'pageVisits' => $pageVisits,
        ]);
    }

    public function update(Request $request, Permiso $permiso)
    {
        if (!Auth::user()->tienePermiso('permisos.editar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para editar permisos'], 403);
            }
            abort(403, 'No tiene permiso para editar permisos');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:permiso,nombre,' . $permiso->id,
        ]);

        $datosAnteriores = [
            'nombre' => $permiso->nombre,
        ];

        $permiso->update($validated);

        \App\Models\Bitacora::create([
            'accion' => 'Permiso actualizado',
            'modulo' => 'Permiso',
            'tabla_afectada' => 'permiso',
            'registro_id' => $permiso->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $validated,
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($permiso);
        }

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso actualizado exitosamente');
    }

    public function destroy(Request $request, Permiso $permiso)
    {
        if (!Auth::user()->tienePermiso('permisos.eliminar')) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'No tiene permiso para eliminar permisos'], 403);
            }
            abort(403, 'No tiene permiso para eliminar permisos');
        }

        $datosAnteriores = [
            'nombre' => $permiso->nombre,
        ];

        $permiso->delete();

        \App\Models\Bitacora::create([
            'accion' => 'Permiso eliminado',
            'modulo' => 'Permiso',
            'tabla_afectada' => 'permiso',
            'registro_id' => $permiso->id,
            'datos_anteriores' => $datosAnteriores,
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(null, 204);
        }

        return redirect()->route('permisos.index')
            ->with('success', 'Permiso eliminado exitosamente');
    }
}
