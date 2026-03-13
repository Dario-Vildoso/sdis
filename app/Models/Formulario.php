<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'asignacions', 'formulario_id', 'variable_id');
    }
}
