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
        Schema::create('asistencia_biometrico', function (Blueprint $table) {
            $table->id();

            $table->date('fecha_actual');
            $table->time('hora');
            // $table->enum('tipo_registro', ['entrada', 'salida']);
            $table->string('tipo_registro')->nullable();                
            $table->boolean('asistencia')->default(true);
            $table->string('observacion')->nullable();

            $table->uuid('id_estudiante');
            $table->uuid('id_calendario')->nullable();

            // $table->unique(['id_estudiante', 'fecha_actual', 'tipo_registro']);

            $table->foreign('id_estudiante')->references('id')->on('estudiante')->onDelete('cascade');
            $table->foreign('id_calendario')->references('id')->on('calendario_admin')->onDelete('cascade');

            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('asistencia_biometrico');
    }
};
