<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissions;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    use HasPermissions;

    public function index()
    {
        $this->autorizarPermiso('roles.ver', 'No tiene permiso para ver roles');

        $roles = Rol::with(['permisos', 'usuarios'])->get();
        $permisos = Permiso::all();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    public function create()
    {
        $this->autorizarPermiso('roles.crear', 'No tiene permiso para crear roles');

        $permisos = Permiso::all();

        return Inertia::render('Roles/Create', [
            'permisos' => $permisos,
        ]);
    }

    public function store(Request $request)
    {
        $this->autorizarPermiso('roles.crear', 'No tiene permiso para crear roles');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:rol,nombre',
            'permisos' => 'array',
            'permisos.*' => 'exists:permiso,id',
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
        ]);

        if (!empty($validated['permisos'])) {
            $rol->permisos()->sync($validated['permisos']);
        }

        \App\Models\Bitacora::create([
            'accion' => 'Rol creado',
            'modulo' => 'Rol',
            'tabla_afectada' => 'rol',
            'registro_id' => $rol->id,
            'datos_nuevos' => $rol->toArray(),
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado exitosamente');
    }

    public function edit(Rol $rol)
    {
        $this->autorizarPermiso('roles.editar', 'No tiene permiso para editar roles');

        $rol->load('permisos');
        $permisos = Permiso::all();

        return Inertia::render('Roles/Edit', [
            'rol' => $rol,
            'permisos' => $permisos,
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $this->autorizarPermiso('roles.editar', 'No tiene permiso para editar roles');

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
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Permisos del rol actualizados exitosamente');
    }

    public function destroy(Rol $rol)
    {
        $this->autorizarPermiso('roles.eliminar', 'No tiene permiso para eliminar roles');

        // No permitir eliminar el rol PROPIETARIO
        if ($rol->nombre === 'PROPIETARIO') {
            return back()->withErrors(['error' => 'No se puede eliminar el rol PROPIETARIO']);
        }

        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar el rol porque tiene usuarios asignados']);
        }

        $datosAnteriores = [
            'id' => $rol->id,
            'nombre' => $rol->nombre,
            'permisos' => $rol->permisos->pluck('id')->toArray(),
        ];

        $rol->permisos()->detach();
        $rol->delete();

        \App\Models\Bitacora::create([
            'accion' => 'Rol eliminado',
            'modulo' => 'Rol',
            'tabla_afectada' => 'rol',
            'registro_id' => $datosAnteriores['id'],
            'datos_anteriores' => $datosAnteriores,
            'usuario_id' => auth()->id(),
            'fecha' => now(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado exitosamente');
    }
}


