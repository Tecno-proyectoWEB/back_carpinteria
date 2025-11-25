<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ruta',
        'icono',
        'orden',
        'activo',
        'parent_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Relación con el menú padre (para submenús)
     */
    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Relación con los menús hijos (submenús)
     */
    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->where('activo', true)->orderBy('orden');
    }

    /**
     * Relación muchos a muchos con roles
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'menu_item_rol', 'menu_item_id', 'rol_id');
    }

    /**
     * Scope para obtener solo items activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener solo items sin padre (menú principal)
     */
    public function scopePrincipales($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Obtener menú para un rol específico
     */
    public static function getMenuForRol($rolId)
    {
        return self::activos()
            ->principales()
            ->whereHas('roles', function ($query) use ($rolId) {
                $query->where('rol.id', $rolId);
            })
            ->with(['children' => function ($query) use ($rolId) {
                $query->whereHas('roles', function ($q) use ($rolId) {
                    $q->where('rol.id', $rolId);
                });
            }])
            ->orderBy('orden')
            ->get();
    }
}

