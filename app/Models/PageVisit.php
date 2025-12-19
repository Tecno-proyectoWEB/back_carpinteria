<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ruta',
        'nombre_pagina',
        'contador',
        'ultima_visita',
    ];

    protected $casts = [
        'contador' => 'integer',
        'ultima_visita' => 'datetime',
    ];

    /**
     * Incrementar contador de visitas para una ruta
     */
    public static function incrementar($ruta, $nombrePagina = null)
    {
        $pageVisit = self::firstOrCreate(
            ['ruta' => $ruta],
            [
                'nombre_pagina' => $nombrePagina ?? $ruta,
                'contador' => 0,
            ]
        );

        $pageVisit->increment('contador');
        $pageVisit->update(['ultima_visita' => now()]);

        return $pageVisit;
    }

    /**
     * Obtener contador de visitas para una ruta
     */
    public static function obtenerContador($ruta)
    {
        $pageVisit = self::where('ruta', $ruta)->first();
        return $pageVisit ? $pageVisit->contador : 0;
    }

    /**
     * Obtener todas las visitas ordenadas por contador
     */
    public static function obtenerMasVisitadas($limit = 10)
    {
        return self::orderBy('contador', 'desc')
            ->limit($limit)
            ->get();
    }
}
