<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido_compra';

    protected $fillable = [
        'cantidad',
        'estado',
        'importe',
        'importe_desc',
        'precio',
        'compra_id',
        'material_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'importe' => 'float',
        'importe_desc' => 'float',
        'precio' => 'float',
    ];

    public $timestamps = false;

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
