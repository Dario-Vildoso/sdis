<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alcance extends Model
{
    protected $fillable = [
        'usuario_id',
        'tipo',
        'alcance_id'
    ];
}
