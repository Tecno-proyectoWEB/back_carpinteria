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

    public function permisos()
    {
        return $this->rol->permisos() ?? collect();
    }

    public function tienePermiso($permiso)
    {
        if (!$this->rol) {
            return false;
        }
        return $this->rol->permisos()->where('nombre', $permiso)->exists();
    }

    public function getAuthIdentifierName()
    {
        return 'email';
    }
}
