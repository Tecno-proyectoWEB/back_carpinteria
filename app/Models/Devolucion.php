<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolucion extends Model
{
    use HasFactory;

    protected $table = 'devolucion';

    protected $fillable = [
        'fecha',
        'motivo',
        'descripcion',
        'importe_total',
        'estado',
        'usuario_id',
        'pedido_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'importe_total' => 'float',
        'estado' => 'boolean',
    ];

    public $timestamps = false;

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleDevolucion::class, 'devolucion_id');
    }
}
