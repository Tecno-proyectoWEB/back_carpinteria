<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacen';

    protected $fillable = [
        'nombre',
        'capacidad',
        'ubicacion',
        'activo',
    ];

    protected $casts = [
        'capacidad' => 'float',
        'activo' => 'boolean',
    ];

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
