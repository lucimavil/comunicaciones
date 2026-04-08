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
      Schema::create('destinatarios', function (Blueprint $table) {

    $table->id();

    $table->foreignId('comunicacion_id')
          ->constrained('comunicaciones')
          ->cascadeOnDelete();

    $table->foreignId('user_id')
         ->constrained('users');

    $table->string('estado')->default('enviado');

    $table->timestamp('leido_at')->nullable();

    $table->string('respuesta')->nullable();

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destinatarios');
    }
};
