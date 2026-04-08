<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comunicacion extends Model
{
    protected $table = 'comunicaciones';
    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'responsable_id',
        'fecha_programada',
        'estado'
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class,'responsable_id');
    }

    public function destinatarios()
    {
        return $this->hasMany(Destinatario::class);
    }
    public function segmentos()
    {
        return $this->belongsToMany(Segmento::class);
    }
}