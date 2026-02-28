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
        Schema::create('egresados', function (Blueprint $table) {

            // Primary key
            $table->uuid('id')->primary();

            // Relaciones
            $table->uuid('id_estudiante');
            $table->uuid('id_grupo');
            $table->uuid('id_especialidad');

            // Foreign keys
            $table->foreign('id_estudiante')
                ->references('id')
                ->on('estudiante')
                ->onDelete('cascade');

            $table->foreign('id_grupo')
                ->references('id')
                ->on('grupo')
                ->onDelete('cascade');

            $table->foreign('id_especialidad')
                ->references('id')
                ->on('especialidad_programa')
                ->onDelete('cascade');

            // Indexes (opcional pero recomendado)
            $table->index('id_estudiante');
            $table->index('id_grupo');
            $table->index('id_especialidad');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresados');
    }
};