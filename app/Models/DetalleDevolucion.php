<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleDevolucion extends Model
{
    use HasFactory;

    protected $table = 'detalle_devolucion';

    protected $fillable = [
        'cantidad',
        'importe_total',
        'motivo_detalle',
        'devolucion_id',
        'detalle_pedido_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'importe_total' => 'float',
    ];

    public $timestamps = false;

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class, 'devolucion_id');
    }

    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'detalle_pedido_id');
    }
}
