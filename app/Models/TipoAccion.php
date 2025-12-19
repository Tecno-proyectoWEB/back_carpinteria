<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoAccion extends Model
{
    use HasFactory;

    protected $table = 'tipo_accion';

    protected $fillable = [
        'codigo',
        'descripcion',
        'modulo',
    ];

    public $timestamps = false;

    public function bitacoras()
    {
        return $this->hasMany(Bitacora::class, 'tipo_accion_id');
    }
}
