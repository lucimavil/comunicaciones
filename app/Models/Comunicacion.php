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
        'solicitante',

        'tipo_segmentacion',
        'segmentacion_sql',
        'cantidad_destinatarios',

        'mensaje',

        'fecha_programada',
        'estado',

        'adjunto_path',
        'adjunto_nombre',
        'adjunto_tipo_mime',
        'tipo_adjunto',
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'cantidad_destinatarios' => 'integer',
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function destinatarios()
    {
        return $this->hasMany(Destinatario::class);
    }

    public function segmentos()
    {
        return $this->belongsToMany(Segmento::class);
    }
    public function puedeEditarse(): bool
    {
        if ($this->estado === 'borrador') {
            return true;
        }

        if ($this->estado === 'programada' && $this->fecha_programada) {
            return Carbon::parse($this->fecha_programada)->isFuture();
        }

        return false;
    }
}