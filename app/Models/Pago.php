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
        'venta_id',
        'metodo_pago_id',
        'usuario_id',
        'nro_pago',
        'nro_transaccion',
        'qr_image',
        'qr_expires_at',
        'fecha_confirmacion',
        'metodo_pago_facil',
    ];

    protected $casts = [
        'monto' => 'float',
        'fecha_pago' => 'datetime',
        'fecha_vencimiento' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'qr_expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public function venta()
    {
        return $this->belongsTo(Venta::class);
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

