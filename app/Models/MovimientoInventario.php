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
        'venta_id',
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

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}

