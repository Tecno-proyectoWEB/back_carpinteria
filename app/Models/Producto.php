<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'stock',
        'stock_minimo',
        'imagen',
        'tiempo',
        'precio_unitario',
        'categoria_id',
    ];

    protected $casts = [
        'precio_unitario' => 'float',
        'stock' => 'integer',
        'stock_minimo' => 'integer',
    ];

    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
