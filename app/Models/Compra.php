<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compra';

    protected $fillable = [
        'estado',
        'fecha',
        'importe_total',
        'importe_descuento',
        'observaciones',
        'proveedor_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'importe_total' => 'float',
        'importe_descuento' => 'float',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
