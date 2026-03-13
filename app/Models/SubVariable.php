<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubVariable extends Model
{
    protected $fillable = ['variable_id', 'descripcion', 'estado'];
}
