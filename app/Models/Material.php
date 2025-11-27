<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    use HasFactory;

    protected $table = 'material';

    protected $fillable = [
        'nombre',
        'descripcion',
        'unidad_medida',
        'precio',
        'stock_actual',
        'stock_minimo',
        'punto_reorden',
        'categoria_text',
        'activo',
        'imagen',
        'categoria_id',
    ];

    protected $casts = [
        'precio' => 'float',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'punto_reorden' => 'integer',
        'activo' => 'boolean',
    ];

    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'material_id');
    }
}

