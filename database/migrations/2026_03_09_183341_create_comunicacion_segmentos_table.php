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
       Schema::create('comunicacion_segmento', function (Blueprint $table) {

            $table->id();

            $table->foreignId('comunicacion_id')
                ->constrained('comunicaciones')
                ->cascadeOnDelete();

            $table->foreignId('segmento_id')
                ->constrained('segmentos')
                ->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunicacion_segmentos');
    }
};
