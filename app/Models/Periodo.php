<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    protected $fillable = ['formulario_id', 'descripcion', 'fecha_inicio', 'fecha_fin', 'estado'];

    protected $casts = [
        'estado' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];
}
