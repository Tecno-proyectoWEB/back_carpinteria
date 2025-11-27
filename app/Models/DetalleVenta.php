<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_venta';

    protected $fillable = [
        'producto_id',
        'servicio_id',
        'venta_id',
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

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}

