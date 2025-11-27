<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_base',
        'tiempo_estimado',
        'activo',
        'categoria_id',
    ];

    protected $casts = [
        'precio_base' => 'float',
        'tiempo_estimado' => 'integer',
        'activo' => 'boolean',
    ];

    public $timestamps = false;

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'servicio_id');
    }
}

