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
    ];

    public $timestamps = false;

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'metodo_pago_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
