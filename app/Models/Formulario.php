<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    protected $fillable = [
        'nombre',
        'gestion_id',
    ];
    public function gestion()
    {
        return $this->belongsTo(Gestion::class);
    }
    public function periodos()
    {
        return $this->hasMany(Periodo::class, 'formulario_id');
    }
    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'asignacions', 'formulario_id', 'variable_id');
    }
}
