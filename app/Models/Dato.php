<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dato extends Model
{
    protected $fillable = [
        'cabecera_id',
        'subvariable_id',
        'periodo_id',
        'establecimiento_id',
        'valor',
        'fecha',
        'hora',
    ];
}
