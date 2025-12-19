<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sector';

    protected $fillable = [
        'nombre',
        'stock',
        'capacidad_maxima',
        'tipo',
        'descripcion',
        'almacen_id',
        'activo',
    ];

    protected $casts = [
        'stock' => 'float',
        'capacidad_maxima' => 'float',
        'activo' => 'boolean',
    ];

    public $timestamps = false;

    public function almacen()
    {
        return $this->belongsTo(\App\Models\Almacen::class);
    }

    public function materiales()
    {
        return $this->hasMany(Material::class);
    }
}
