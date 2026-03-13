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
}
