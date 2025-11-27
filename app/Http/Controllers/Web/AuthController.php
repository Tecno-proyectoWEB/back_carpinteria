<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Usuario;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return Inertia::render('Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $usuario = Usuario::where('email', $request->email)->with(['rol.permisos'])->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'message' => 'Credenciales inválidas',
            ]);
        }

        // Validar todos los estados de seguridad según modelo de negocio
        if (!$usuario->estado) {
            return back()->withErrors(['message' => 'Usuario inactivo']);
        }

        if (!$usuario->disponibilidad) {
            return back()->withErrors(['message' => 'Usuario no disponible']);
        }

        if (!$usuario->cuenta_no_expirada) {
            return back()->withErrors(['message' => 'Cuenta expirada']);
        }

        if (!$usuario->cuenta_no_bloqueada) {
            return back()->withErrors(['message' => 'Cuenta bloqueada']);
        }

        if (!$usuario->credenciales_no_expiradas) {
            return back()->withErrors(['message' => 'Credenciales expiradas']);
        }

        // Asegurar que el rol y permisos estén cargados antes de hacer login
        if (!$usuario->relationLoaded('rol')) {
            $usuario->load('rol');
        }
        if ($usuario->rol && !$usuario->rol->relationLoaded('permisos')) {
            $usuario->rol->load('permisos');
        }

        // Hacer login y guardar la sesión
        Auth::login($usuario, $request->filled('remember')); // Opcional: recordar sesión
        
        // Guardar la sesión explícitamente
        $request->session()->save();
        
        // Regenerar el ID de sesión por seguridad (después de guardar)
        $request->session()->regenerate();
        
        // Verificar que el usuario esté autenticado después del login
        $isAuthenticated = Auth::check();
        $authenticatedUser = Auth::user();
        
        \Log::info('Usuario autenticado exitosamente', [
            'usuario_id' => $usuario->id,
            'email' => $usuario->email,
            'rol' => $usuario->rol->nombre ?? 'Sin rol',
            'session_id' => $request->session()->getId(),
            'auth_check' => $isAuthenticated,
            'auth_user_id' => $authenticatedUser ? $authenticatedUser->id : null,
        ]);

        // Guardar la sesión una vez más antes del redirect
        $request->session()->save();
        
        \Log::debug('Redirigiendo al dashboard', [
            'session_id' => $request->session()->getId(),
            'auth_check_antes_redirect' => Auth::check(),
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forceLogout(Request $request)
    {
        // Cerrar sesión sin importar el estado
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Limpiar todas las cookies de sesión
        $request->session()->flush();

        // Redirigir al login
        return redirect()->route('login')->with('success', 'Sesión cerrada exitosamente');
    }
}

