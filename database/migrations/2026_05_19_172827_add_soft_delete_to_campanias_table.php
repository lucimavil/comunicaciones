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
        Schema::table('campanias', function (Blueprint $table) {

            // soft delete
            $table->softDeletes();

            // opcional: estado eliminada
            $table->string('estado')->default('eliminada')->change();
  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campanias', function (Blueprint $table) {

            $table->dropSoftDeletes();

        });
    }
};