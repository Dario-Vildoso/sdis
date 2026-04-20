<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establecimiento_Formulario extends Model
{
    protected $table = 'establecimiento_formularios';

    protected $fillable = [
        'establecimiento_id',
        'formulario_id',
    ];

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class, 'establecimiento_id');
    }

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }
}
