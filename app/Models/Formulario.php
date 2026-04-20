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
    public function sistema()
    {
        return $this->belongsTo(Sistema::class);
    }
     public function asignaciones()
    {
        return $this->hasMany(Asignacion::class, 'formulario_id');
    }
    public function variables()
    {
        return $this->belongsToMany(Variable::class, 'asignacions', 'formulario_id', 'variable_id');
    }
    public function establecimientos()
    {
        return $this->belongsToMany(Establecimiento::class, 'establecimiento_formularios', 'formulario_id', 'establecimiento_id');
    }
}
