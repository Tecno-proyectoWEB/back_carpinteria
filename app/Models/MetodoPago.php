<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodo_pago';

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_electronico',
        'tipo_electronico',
        'numero_cuenta',
        'entidad_financiera',
        'activo',
    ];

    public $timestamps = false;

    public function ventas()
    {
        return $this->hasMany(\App\Models\Venta::class, 'metodo_pago_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
