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
        Schema::create('competencias', function (Blueprint $table) {
           $table->uuid('id')->primary();

            // UUID que viene de la tabla modulos
            $table->uuid('id_modulo');

            // Tipo de competencia
            $table->char('tipo');

            // Descripción del tipo de competencia
            $table->text('descripcion');

            $table->timestamps();

            // Si deseas clave foránea real (recomendado)
            $table->foreign('id_modulo')->references('id')->on('modulos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competencias');
    }
};
