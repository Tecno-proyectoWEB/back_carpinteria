<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalle_compra';

    protected $fillable = [
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descuento',
        'importe_total',
        'compra_id',
        'material_id',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'float',
        'subtotal' => 'float',
        'descuento' => 'float',
        'importe_total' => 'float',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
