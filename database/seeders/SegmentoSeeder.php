<?php

namespace Database\Seeders;

use App\Models\Segmento;
use Illuminate\Database\Seeder;

class SegmentoSeeder extends Seeder
{
    public function run(): void
    {
        $segmentos = [
            [
                'codigo' => 'MEDICOS',
                'nombre' => 'Médicos',
                'descripcion' => 'Profesionales con especialidades médicas.',
                'orden' => 1,
            ],
            [
                'codigo' => 'ENFERMERIA',
                'nombre' => 'Enfermería',
                'descripcion' => 'Personal de enfermería.',
                'orden' => 2,
            ],
            [
                'codigo' => 'KINESIOLOGIA',
                'nombre' => 'Kinesiología',
                'descripcion' => 'Profesionales de kinesiología.',
                'orden' => 3,
            ],
            [
                'codigo' => 'LABORATORIO',
                'nombre' => 'Laboratorio',
                'descripcion' => 'Personal de laboratorio y áreas relacionadas.',
                'orden' => 4,
            ],
            [
                'codigo' => 'FARMACIA',
                'nombre' => 'Farmacia',
                'descripcion' => 'Personal del área de farmacia.',
                'orden' => 5,
            ],
            [
                'codigo' => 'NUTRICION',
                'nombre' => 'Nutrición',
                'descripcion' => 'Profesionales de nutrición.',
                'orden' => 6,
            ],
            [
                'codigo' => 'PSICOLOGIA',
                'nombre' => 'Psicología',
                'descripcion' => 'Profesionales de psicología.',
                'orden' => 7,
            ],
            [
                'codigo' => 'TRABAJO_SOCIAL',
                'nombre' => 'Trabajo Social',
                'descripcion' => 'Profesionales de trabajo social.',
                'orden' => 8,
            ],
            [
                'codigo' => 'TECNICOS',
                'nombre' => 'Técnicos',
                'descripcion' => 'Personal técnico asistencial.',
                'orden' => 9,
            ],
            [
                'codigo' => 'ADMINISTRATIVOS',
                'nombre' => 'Administrativos',
                'descripcion' => 'Personal administrativo.',
                'orden' => 10,
            ],
            [
                'codigo' => 'DIRECTIVOS',
                'nombre' => 'Directivos',
                'descripcion' => 'Directores, jefes y responsables institucionales.',
                'orden' => 11,
            ],
        ];

        foreach ($segmentos as $segmento) {
            Segmento::updateOrCreate(
                ['codigo' => $segmento['codigo']],
                $segmento
            );
        }
    }
}