<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pago';

    protected $fillable = [
        'monto',
        'fecha_pago',
        'fecha_vencimiento',
        'estado', // 'PENDIENTE', 'PAGADO', 'VENCIDO', 'CANCELADO'
        'tipo', // 'CONTADO', 'CREDITO', 'CUOTA'
        'numero_cuota',
        'observaciones',
        'pedido_id',
        'metodo_pago_id',
        'usuario_id',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha_pago' => 'datetime',
        'fecha_vencimiento' => 'datetime',
    ];

    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}

