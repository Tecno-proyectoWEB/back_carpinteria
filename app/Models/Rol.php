<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';

    protected $fillable = [
        'nombre',
    ];

    /**
     * This table does not have the default Laravel timestamps.
     */
    public $timestamps = false;

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permiso', 'rol_id', 'permiso_id');
    }

    /**
     * Verificar si el rol tiene un permiso específico
     */
    public function tienePermiso($permiso)
    {
        // Si los permisos ya están cargados, usar la colección en memoria
        if ($this->relationLoaded('permisos') && $this->permisos) {
            return $this->permisos->contains('nombre', $permiso);
        }

        // Si no están cargados, hacer consulta a la BD
        return $this->permisos()->where('nombre', $permiso)->exists();
    }
}
