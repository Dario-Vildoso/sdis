<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabecera extends Model
{

    protected $fillable = ['descripcion', 'cabecera_id', 'variable_id', 'estado'];
    public function hijos()
    {
        return $this->hasMany(Cabecera::class, 'cabecera_id')->with('hijos');
    }

    public function padre()
    {
        return $this->belongsTo(Cabecera::class, 'cabecera_id');
    }
}
