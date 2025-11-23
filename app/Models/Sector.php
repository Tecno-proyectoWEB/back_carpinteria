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
    ];

    protected $casts = [
        'stock' => 'float',
        'capacidad_maxima' => 'float',
    ];

    public $timestamps = false;

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function materiales()
    {
        return $this->hasMany(Material::class);
    }
}
