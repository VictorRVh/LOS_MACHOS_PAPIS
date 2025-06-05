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
        Schema::create('asistencia', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('fecha_actual');
            $table->boolean('asistencia');
            $table->string('observacion')->nullable();
            
            $table->uuid('id_grupo');
            $table->uuid('id_estudiante');
            $table->uuid('id_calendario');

            $table->foreign('id_grupo')
                  ->references('id')->on('grupo')
                  ->onDelete('cascade');

            $table->foreign('id_estudiante')
                  ->references('id')->on('estudiante')
                  ->onDelete('cascade');

            $table->foreign('id_calendario')
                  ->references('id')->on('calendario_admin')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia');
    }
};
