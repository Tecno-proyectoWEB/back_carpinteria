<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventario';

    protected $fillable = [
        'tipo', // 'INGRESO' o 'SALIDA'
        'cantidad',
        'motivo',
        'observaciones',
        'fecha',
        'material_id',
        'producto_id',
        'usuario_id',
        'compra_id',
        'pedido_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'fecha' => 'datetime',
    ];

    public $timestamps = false;

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}

