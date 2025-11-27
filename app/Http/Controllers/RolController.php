<?php

namespace App\Http\Controllers;

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
            return back()->withErrors(['message' => 'No tiene permiso para ver roles']);
        }

        return Inertia::render('Roles/Index', [
            'roles' => Rol::with(['usuarios', 'permisos'])->get(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('roles.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear roles']);
        }

        return Inertia::render('Roles/Create', [
            'permisos' => Permiso::all(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('roles.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear roles']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255|unique:rol,nombre',
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $rol = Rol::create([
            'nombre' => $request->nombre,
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        }

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente');
    }

    public function edit($role)
    {
        if (!Auth::user()->tienePermiso('roles.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar roles']);
        }

        // Si $role es un string (ID), buscar el rol
        if (is_string($role) || is_numeric($role)) {
            $rol = Rol::findOrFail($role);
        } else {
            $rol = $role;
        }

        // Cargar el rol con sus permisos
        $rol->load('permisos');

        return Inertia::render('Roles/Edit', [
            'rol' => [
                'id' => $rol->id,
                'nombre' => $rol->nombre,
                'permisos' => $rol->permisos->map(function($permiso) {
                    return [
                        'id' => $permiso->id,
                        'nombre' => $permiso->nombre,
                    ];
                })->values()->toArray(),
            ],
            'permisos' => Permiso::all(),
        ]);
    }

    public function update(Request $request, $role)
    {
        if (!Auth::user()->tienePermiso('roles.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar roles']);
        }

        // Si $role es un string (ID), buscar el rol
        if (is_string($role) || is_numeric($role)) {
            $rol = Rol::findOrFail($role);
        } else {
            $rol = $role;
        }

        $request->validate([
            'nombre' => 'required|string|max:255|unique:rol,nombre,' . $rol->id,
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $rol->update([
            'nombre' => $request->nombre,
        ]);

        // Sincronizar permisos (si viene como array vacío, elimina todos los permisos)
        $permisos = $request->input('permisos', []);
        $rol->permisos()->sync($permisos);

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
    }

    public function destroy(Rol $rol)
    {
        if (!Auth::user()->tienePermiso('roles.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar roles']);
        }

        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar el rol porque tiene usuarios asignados']);
        }

        $rol->permisos()->detach();
        $rol->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
    }
}
