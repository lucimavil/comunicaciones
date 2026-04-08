<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comunicaciones', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('tipo');
    $table->text('descripcion')->nullable();
    $table->foreignId('responsable_id')->constrained('users');
    $table->timestamp('fecha_programada')->nullable();
    $table->string('estado')->default('borrador');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicaciones');
    }
};
