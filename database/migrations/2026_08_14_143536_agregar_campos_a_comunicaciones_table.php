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
    Schema::table('comunicaciones', function (Blueprint $table) {

        $table->string('solicitante')->nullable();

        $table->string('tipo_segmentacion')->nullable();
        $table->longText('segmentacion_sql')->nullable();
        $table->unsignedInteger('cantidad_destinatarios')->default(0);

        $table->text('mensaje')->nullable();

        $table->string('adjunto_path')->nullable();
        $table->string('adjunto_nombre')->nullable();
        $table->string('adjunto_tipo_mime')->nullable();
        $table->string('tipo_adjunto')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('comunicaciones', function (Blueprint $table) {
        $table->dropColumn([
            'solicitante',
            'tipo_segmentacion',
            'segmentacion_sql',
            'cantidad_destinatarios',
            'mensaje',
            'adjunto_path',
            'adjunto_nombre',
            'adjunto_tipo_mime',
            'tipo_adjunto',
        ]);
    });
}
};
