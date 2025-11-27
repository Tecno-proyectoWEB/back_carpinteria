<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use App\Models\Rol;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $request->email)->with('rol')->first();

        if (!$usuario) {
            return back()->withErrors(['email' => 'Credenciales inválidas']);
        }

        // Obtener el hash original de la base de datos (sin el cast)
        $passwordHash = $usuario->getRawOriginal('password');
        
        if (!Hash::check($request->password, $passwordHash)) {
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

        // Autenticar al usuario (sin remember_token ya que la tabla no lo tiene)
        Auth::login($usuario, false);
        
        // Regenerar ID de sesión para seguridad
        $request->session()->regenerate();

        Log::info('Login exitoso', [
            'user_id' => $usuario->id,
            'email' => $usuario->email,
            'rol' => $usuario->rol ? $usuario->rol->nombre : null,
            'is_authenticated' => Auth::check(),
            'session_id' => $request->session()->getId()
        ]);

        // Obtener la URL de destino
        $intendedUrl = $request->session()->pull('url.intended', route('dashboard'));

        Log::info('Redirigiendo a', [
            'url' => $intendedUrl,
            'is_inertia' => $request->header('X-Inertia'),
            'auth_check' => Auth::check(),
            'user_id' => Auth::id()
        ]);

        // Para peticiones de Inertia, usar Inertia::location() que fuerza una redirección HTTP completa
        // Esto hace que el navegador haga una petición GET completa en lugar de una petición AJAX
        if ($request->header('X-Inertia')) {
            // Inertia::location() devuelve una respuesta 409 con header X-Inertia-Location
            // que Inertia.js interpreta como una redirección completa del navegador
            return Inertia::location($intendedUrl);
        }

        // Para peticiones normales (no Inertia), redirección estándar
        return redirect($intendedUrl);
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
            'password' => $request->password, // El cast 'hashed' lo hasheará automáticamente
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
