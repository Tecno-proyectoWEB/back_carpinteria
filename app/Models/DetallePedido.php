<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    use HasFactory;

    protected $table = 'detalle_pedido';

    protected $fillable = [
        'producto_id',
        'servicio_id',
        'pedido_id',
        'cantidad',
        'estado',
        'importe_total',
        'importe_total_desc',
        'precio_unitario',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'estado' => 'boolean',
        'importe_total' => 'float',
        'importe_total_desc' => 'float',
        'precio_unitario' => 'float',
    ];

    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function detalleDevolucion()
    {
        return $this->hasOne(DetalleDevolucion::class);
    }
}
