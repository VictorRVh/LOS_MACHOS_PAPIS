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
        Schema::create('capacidades_terminales_competencia', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // UUID que viene de la tabla modulos
            $table->uuid('id_competencia');

            $table->string('sigla')->nullable();

            $table->string('descripcion');

            $table->timestamps();

            // Si deseas clave foránea real (recomendado)
            $table->foreign('id_competencia')->references('id')->on('competencias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacidades_terminales_competencia');
    }
};
