<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedido';

    protected $fillable = [
        'fecha',
        'descripcion',
        'importe_total',
        'importe_total_desc',
        'estado',
        'metodo_pago_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'importe_total' => 'float',
        'importe_total_desc' => 'float',
        'estado' => 'boolean',
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'metodo_pago_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }
}
