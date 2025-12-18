<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next, ...$guards)
    {
        Log::info('🔐 Authenticate middleware ejecutándose', [
            'path' => $request->path(),
            'method' => $request->method(),
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
            'auth_check_before' => Auth::check(),
            'auth_id_before' => Auth::id(),
            'cookies' => array_keys($request->cookies->all()),
        ]);

        $result = parent::handle($request, $next, ...$guards);

        Log::info('🔐 Authenticate middleware completado', [
            'auth_check_after' => Auth::check(),
            'auth_id_after' => Auth::id(),
        ]);

        return $result;
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        Log::warning('❌ Usuario NO autenticado - redirigiendo a login', [
            'path' => $request->path(),
            'session_id' => $request->session()->getId(),
        ]);
        
        return $request->expectsJson() ? null : route('login');
    }
}

