<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanias', function (Blueprint $table) {
            $table->id();

            // 🧾 DATOS GENERALES
            $table->string('titulo');
            $table->text('descripcion')->nullable();

            $table->foreignId('responsable_id')->constrained('users');

            // Si solicitante es usuario → mejor FK
             $table->string('solicitante')->nullable();

            // 📊 ESTADO
            $table->string('estado')->default('borrador'); // borrador | programada | finalizada

            // 🧠 SEGMENTACIÓN
            $table->string('segmentacion_tipo')->default('filtros'); // filtros | sql

            $table->unsignedInteger('edad_min')->nullable();
            $table->unsignedInteger('edad_max')->nullable();

            $table->string('sexo')->nullable();
            $table->string('localidad')->nullable();
            $table->string('diagnostico')->nullable();

            $table->date('ultima_atencion_desde')->nullable();
            $table->date('ultima_atencion_hasta')->nullable();

            // SQL FINAL USADA
            $table->longText('segmentacion_sql')->nullable();

            // RESULTADO
            $table->unsignedInteger('cantidad_destinatarios')->nullable();

            // 💬 MENSAJE
            $table->text('mensaje')->nullable();

            // 📎 ADJUNTO
            $table->string('adjunto_path')->nullable();
            $table->string('adjunto_nombre')->nullable();
            $table->string('adjunto_tipo_mime')->nullable();
            $table->string('tipo_adjunto')->nullable(); // imagen | documento

            // 📅 PROGRAMACIÓN
            $table->dateTime('fecha_programada')->nullable();

            // 📅 FECHAS GENERALES (si las usás)
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanias');
    }
};