<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    use HasFactory;

    protected $table = 'bitacora';

    protected $fillable = [
        'accion',
        'modulo',
        'tabla_afectada',
        'registro_id',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
        'direccion_ip',
        'navegador',
        'fecha',
        'tipo_accion_id',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha' => 'datetime',
    ];

    public $timestamps = false;

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function tipoAccion()
    {
        return $this->belongsTo(TipoAccion::class, 'tipo_accion_id');
    }
}
