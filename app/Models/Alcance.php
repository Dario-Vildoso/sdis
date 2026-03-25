<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alcance extends Model
{
    protected $fillable = [
        'user_id',
        'scope_type',
        'scope_id'
    ];
}
