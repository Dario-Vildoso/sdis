<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $fillable = ['descripcion'];
    public function cabeceras()
    {
        return $this->hasMany(Cabecera::class, 'variable_id');
    }
    public function subvariables()
    {
        return $this->hasMany(SubVariable::class, 'variable_id');
    }
    public function formularios()
    {
        return $this->belongsToMany(Formulario::class, 'asignacions', 'variable_id', 'formulario_id');
    }
}
