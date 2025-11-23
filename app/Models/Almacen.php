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
    ];

    protected $casts = [
        'capacidad' => 'float',
    ];

    public $timestamps = false;

    public function sectores()
    {
        return $this->hasMany(Sector::class);
    }
}
