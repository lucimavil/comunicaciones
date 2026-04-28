<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
 use Carbon\Carbon;

class Campania extends Model
{
    protected $fillable = [
        // 🧾 Datos generales
        'titulo',
        'descripcion',
        'responsable_id',
        'solicitante',

        // 📊 Estado
        'estado',

        // 🧠 Segmentación
        'segmentacion_tipo',
        'edad_min',
        'edad_max',
        'sexo',
        'localidad',
        'diagnostico',
        'ultima_atencion_desde',
        'ultima_atencion_hasta',
        'segmentacion_sql',
        'cantidad_destinatarios',

        // 💬 Mensaje
        'mensaje',

        // 📎 Adjuntos
        'adjunto_path',
        'adjunto_nombre',
        'adjunto_tipo_mime',
        'tipo_adjunto',

        // 📅 Programación
        'fecha_programada',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'edad_min' => 'integer',
        'edad_max' => 'integer',
        'cantidad_destinatarios' => 'integer',

        'ultima_atencion_desde' => 'date',
        'ultima_atencion_hasta' => 'date',

        'fecha_programada' => 'datetime',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];
    

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }



    /*
    |--------------------------------------------------------------------------
    | HELPERS (muy útiles después)
    |--------------------------------------------------------------------------
    */

    public function esBorrador(): bool
    {
        return $this->estado === 'borrador';
    }

    public function estaProgramada(): bool
    {
        return $this->estado === 'programada';
    }

    public function estaFinalizada(): bool
    {
        return $this->estado === 'finalizada';
    }

    public function tieneAdjunto(): bool
    {
        return !empty($this->adjunto_path);
    }

    public function esImagen(): bool
    {
        return $this->tipo_adjunto === 'imagen';
    }

    public function esDocumento(): bool
    {
        return $this->tipo_adjunto === 'documento';
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

    public function estaVencidaParaEdicion(): bool
    {
        return $this->estado === 'programada'
            && $this->fecha_programada
            && Carbon::parse($this->fecha_programada)->isPast();
    }
}