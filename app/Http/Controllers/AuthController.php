<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->with('rol')->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->withErrors(['email' => 'Credenciales inválidas']);
        }

        // Validar todos los estados de seguridad según modelo de negocio
        if (!$usuario->estado) {
            return back()->withErrors(['email' => 'Usuario inactivo']);
        }

        if (!$usuario->disponibilidad) {
            return back()->withErrors(['email' => 'Usuario no disponible']);
        }

        if (!$usuario->cuenta_no_expirada) {
            return back()->withErrors(['email' => 'Cuenta expirada']);
        }

        if (!$usuario->cuenta_no_bloqueada) {
            return back()->withErrors(['email' => 'Cuenta bloqueada']);
        }

        if (!$usuario->credenciales_no_expiradas) {
            return back()->withErrors(['email' => 'Credenciales expiradas']);
        }

        Auth::login($usuario);

        return redirect()->route('dashboard');
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:usuario,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'nullable|string|max:20',
            'rol_id' => 'required|exists:rol,id',
        ]);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'rol_id' => $request->rol_id,
            'estado' => true,
            'disponibilidad' => true,
            'cuenta_no_expirada' => true,
            'cuenta_no_bloqueada' => true,
            'credenciales_no_expiradas' => true,
        ]);

        $token = $usuario->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $usuario->load('rol'),
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('rol'));
    }
}
