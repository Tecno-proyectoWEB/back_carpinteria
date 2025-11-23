<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categoria';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
        'subcategoria_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public $timestamps = false;

    public function subcategoria()
    {
        return $this->belongsTo(Subcategoria::class, 'subcategoria_id');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }

    public function materiales()
    {
        return $this->hasMany(Material::class);
    }
}
