<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Excepcion extends Model
{
    protected $table = 'excepcions';

    protected $fillable = [
        'subvariable_id',
        'cabecera_id',
    ];
}
