<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

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
        'sector_id',
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

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function detallesCompra()
    {
        return $this->hasMany(DetallePedidoCompra::class, 'material_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class, 'material_id');
    }
}

