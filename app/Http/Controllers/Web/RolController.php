<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RolController extends Controller
{
    public function index()
    {
        if (!Auth::user()->tienePermiso('roles.ver')) {
            abort(403, 'No tiene permiso para ver roles');
        }

        $roles = Rol::with('permisos')->get();
        $permisos = Permiso::all();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    public function edit(Rol $rol)
    {
        if (!Auth::user()->tienePermiso('roles.editar')) {
            abort(403, 'No tiene permiso para editar roles');
        }

        $rol->load('permisos');
        $permisos = Permiso::all();

        return Inertia::render('Roles/Edit', [
            'rol' => $rol,
            'permisos' => $permisos,
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        if (!Auth::user()->tienePermiso('roles.editar')) {
            abort(403, 'No tiene permiso para editar roles');
        }

        $validated = $request->validate([
            'permisos' => 'array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $datosAnteriores = [
            'permisos' => $rol->permisos->pluck('id')->toArray(),
        ];

        $rol->permisos()->sync($validated['permisos'] ?? []);

        \App\Models\Bitacora::create([
            'accion' => 'Permisos de rol actualizados',
            'modulo' => 'Rol',
            'tabla_afectada' => 'rol_permiso',
            'registro_id' => $rol->id,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => ['permisos' => $validated['permisos'] ?? []],
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Permisos del rol actualizados exitosamente');
    }
}


