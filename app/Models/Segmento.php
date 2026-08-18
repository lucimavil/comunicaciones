<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Segmento extends Model
{
    protected $table = 'segmentos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}