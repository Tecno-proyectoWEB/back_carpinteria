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
        if (!Auth::user()->tienePermiso('usuarios.ver')) {
            return back()->withErrors(['message' => 'No tiene permiso para ver roles']);
        }

        return Inertia::render('Roles/Index', [
            'roles' => Rol::with(['usuarios', 'permisos'])->get(),
        ]);
    }

    public function create()
    {
        if (!Auth::user()->tienePermiso('usuarios.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear roles']);
        }

        return Inertia::render('Roles/Create', [
            'permisos' => Permiso::all(),
        ]);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->tienePermiso('usuarios.crear')) {
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

    public function edit(Rol $rol)
    {
        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar roles']);
        }

        return Inertia::render('Roles/Edit', [
            'rol' => $rol->load('permisos'),
            'permisos' => Permiso::all(),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar roles']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255|unique:rol,nombre,' . $rol->id,
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $rol->update([
            'nombre' => $request->nombre,
        ]);

        if ($request->has('permisos')) {
            $rol->permisos()->sync($request->permisos);
        } else {
            $rol->permisos()->detach();
        }

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente');
    }

    public function destroy(Rol $rol)
    {
        if (!Auth::user()->tienePermiso('usuarios.eliminar')) {
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
