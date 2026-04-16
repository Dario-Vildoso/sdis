<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sistemas');
    }
}
