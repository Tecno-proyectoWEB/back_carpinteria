<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasPermissions
{
    /**
     * Verifica si el usuario autenticado tiene un permiso específico
     * El PROPIETARIO tiene acceso a todo automáticamente
     */
    protected function tienePermiso($permiso)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->tienePermiso($permiso);
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     */
    protected function tieneAlgunPermiso(array $permisos)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->tieneAlgunPermiso($permisos);
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     */
    protected function tieneTodosLosPermisos(array $permisos)
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->tieneTodosLosPermisos($permisos);
    }

    /**
     * Aborta la petición si el usuario no tiene el permiso
     */
    protected function autorizarPermiso($permiso, $mensaje = 'No tiene permiso para realizar esta acción')
    {
        if (!$this->tienePermiso($permiso)) {
            abort(403, $mensaje);
        }
    }
}

