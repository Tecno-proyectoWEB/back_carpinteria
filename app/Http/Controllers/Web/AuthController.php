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

        // Cargar usuario con rol y permisos
        $usuario = Usuario::where('email', $request->email)->with('rol.permisos')->first();

        if (!$usuario) {
            return back()->withErrors([
                'message' => 'Credenciales inválidas',
            ])->withInput($request->only('email'));
        }

        // Verificar contraseña - usar getRawOriginal para obtener el valor sin el cast
        if (!Hash::check($request->password, $usuario->getRawOriginal('password'))) {
            return back()->withErrors([
                'message' => 'Credenciales inválidas',
            ])->withInput($request->only('email'));
        }

        // Validar todos los estados de seguridad según modelo de negocio
        if (!$usuario->estado) {
            return back()->withErrors(['message' => 'Usuario inactivo'])->withInput($request->only('email'));
        }

        if (!$usuario->disponibilidad) {
            return back()->withErrors(['message' => 'Usuario no disponible'])->withInput($request->only('email'));
        }

        if (!$usuario->cuenta_no_expirada) {
            return back()->withErrors(['message' => 'Cuenta expirada'])->withInput($request->only('email'));
        }

        if (!$usuario->cuenta_no_bloqueada) {
            return back()->withErrors(['message' => 'Cuenta bloqueada'])->withInput($request->only('email'));
        }

        if (!$usuario->credenciales_no_expiradas) {
            return back()->withErrors(['message' => 'Credenciales expiradas'])->withInput($request->only('email'));
        }

        // Autenticar al usuario
        Auth::login($usuario, $request->boolean('remember', false));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

