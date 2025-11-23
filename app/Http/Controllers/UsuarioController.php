<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UsuarioController extends Controller
{
    public function index()
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.ver')) {
            return response()->json(['message' => 'No tiene permiso para ver usuarios'], 403);
        }

        return response()->json(Usuario::with('rol')->get());
    }

    public function store(Request $request)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.crear')) {
            return response()->json(['message' => 'No tiene permiso para crear usuarios'], 403);
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

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Usuario creado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_nuevos' => $usuario->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($usuario->load('rol'), 201);
    }

    public function show(Usuario $usuario)
    {
        return response()->json($usuario->load('rol'));
    }

    public function update(Request $request, Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.editar')) {
            return response()->json(['message' => 'No tiene permiso para editar usuarios'], 403);
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

        $datos_anteriores = $usuario->toArray();
        $usuario->update($request->all());

        // Registrar en bitácora
        \App\Models\Bitacora::create([
            'accion' => 'Usuario actualizado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $datos_anteriores,
            'datos_nuevos' => $usuario->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        return response()->json($usuario->load('rol'));
    }

    public function destroy(Usuario $usuario)
    {
        // Validar permisos
        if (!Auth::user()->tienePermiso('usuarios.eliminar')) {
            return response()->json(['message' => 'No tiene permiso para eliminar usuarios'], 403);
        }

        // No permitir auto-eliminación
        if ($usuario->id === Auth::id()) {
            return response()->json(['error' => 'No puede eliminarse a sí mismo'], 400);
        }

        // Registrar en bitácora antes de eliminar
        \App\Models\Bitacora::create([
            'accion' => 'Usuario eliminado',
            'modulo' => 'Usuario',
            'tabla_afectada' => 'usuario',
            'registro_id' => $usuario->id,
            'datos_anteriores' => $usuario->toArray(),
            'usuario_id' => Auth::id(),
            'fecha' => now(),
        ]);

        $usuario->delete();
        return response()->json(null, 204);
    }
}
