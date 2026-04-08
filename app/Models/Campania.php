<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campania extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'responsable_id'
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class,'responsable_id');
    }
}