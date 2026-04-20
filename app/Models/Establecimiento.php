<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Establecimiento extends Model
{
    protected $table = 'establecimientos';

    protected $fillable = [
        'descripcion',
        'id_mun',
        'codigo_est',
        'gestion',
        'nivel',
        'estado',
    ];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_mun');
    }

    public function formularios()
    {
        return $this->belongsToMany(Formulario::class, 'establecimiento_formularios', 'establecimiento_id', 'formulario_id');
    }
}
