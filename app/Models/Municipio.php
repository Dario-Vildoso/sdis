<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    public function red()
    {
        return $this->belongsTo(Red::class, 'red_id');
    }
}
