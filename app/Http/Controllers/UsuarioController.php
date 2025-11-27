<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UsuarioController extends Controller
{
    public function index()
    {
        return Inertia::render('Usuarios/Index', [
            'usuarios' => Usuario::with('rol')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Create', [
            'roles' => Rol::all(),
        ]);
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.crear')) {
            return back()->withErrors(['message' => 'No tiene permiso para crear usuarios']);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuario',
            'telefono' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'rol_id' => 'required|exists:rol,id',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
            'estado' => $request->estado ?? true,
            'disponibilidad' => $request->disponibilidad ?? true,
            'cuenta_no_expirada' => $request->cuenta_no_expirada ?? true,
            'cuenta_no_bloqueada' => $request->cuenta_no_bloqueada ?? true,
            'credenciales_no_expiradas' => $request->credenciales_no_expiradas ?? true,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente');
    }

    public function show(Usuario $usuario)
    {
        return Inertia::render('Usuarios/Show', [
            'usuario' => $usuario->load('rol'),
        ]);
    }

    public function edit(Usuario $usuario)
    {
        return Inertia::render('Usuarios/Edit', [
            'usuario' => $usuario->load('rol'),
            'roles' => Rol::all(),
        ]);
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            return back()->withErrors(['message' => 'No tiene permiso para editar usuarios']);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:usuario,email,' . $usuario->id,
            'telefono' => 'nullable|string|max:255',
            'password' => 'sometimes|required|string|min:8',
            'rol_id' => 'sometimes|required|exists:rol,id',
            'estado' => 'boolean',
            'disponibilidad' => 'boolean',
            'cuenta_no_expirada' => 'boolean',
            'cuenta_no_bloqueada' => 'boolean',
            'credenciales_no_expiradas' => 'boolean',
        ]);

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $usuario->update($request->all());

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente');
    }

    public function destroy(Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.eliminar')) {
            return back()->withErrors(['message' => 'No tiene permiso para eliminar usuarios']);
        }

        // No permitir auto-eliminación
        if ($usuario->id === Auth::id()) {
            return back()->withErrors(['error' => 'No puede eliminarse a sí mismo']);
        }

        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente');
    }
}
