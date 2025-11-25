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

        $usuario = Usuario::where('email', $request->email)->with('rol')->first();

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

        Auth::login($usuario);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

