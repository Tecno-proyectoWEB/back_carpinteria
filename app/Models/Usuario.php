<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'password',
        'estado',
        'disponibilidad',
        'cuenta_no_expirada',
        'cuenta_no_bloqueada',
        'credenciales_no_expiradas',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'estado' => 'boolean',
        'disponibilidad' => 'boolean',
        'cuenta_no_expirada' => 'boolean',
        'cuenta_no_bloqueada' => 'boolean',
        'credenciales_no_expiradas' => 'boolean',
    ];

    /**
     * The `usuario` table does not include Laravel's default timestamps.
     */
    public $timestamps = false;

    public function rol()
    {
        return $this->belongsTo(Rol::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    /**
     * Obtiene los permisos del usuario a través de su rol
     */
    public function permisos()
    {
        if (!$this->rol) {
            return collect();
        }
        
        // Cargar la relación si no está cargada
        if (!$this->relationLoaded('rol')) {
            $this->load('rol.permisos');
        }
        
        return $this->rol->permisos ?? collect();
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     * El PROPIETARIO tiene acceso a todo automáticamente
     */
    public function tienePermiso($permiso)
    {
        // Si no tiene rol, no tiene permisos
        if (!$this->rol) {
            return false;
        }
        
        // El PROPIETARIO tiene acceso a todo
        if ($this->rol->nombre === 'PROPIETARIO') {
            return true;
        }
        
        // Cargar permisos si no están cargados
        if (!$this->rol->relationLoaded('permisos')) {
            $this->rol->load('permisos');
        }
        
        // Verificar si el rol tiene el permiso
        return $this->rol->permisos->contains('nombre', $permiso);
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     */
    public function tieneAlgunPermiso(array $permisos)
    {
        foreach ($permisos as $permiso) {
            if ($this->tienePermiso($permiso)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     */
    public function tieneTodosLosPermisos(array $permisos)
    {
        foreach ($permisos as $permiso) {
            if (!$this->tienePermiso($permiso)) {
                return false;
            }
        }
        return true;
    }

    public function getAuthIdentifierName()
    {
        return 'email';
    }
}
