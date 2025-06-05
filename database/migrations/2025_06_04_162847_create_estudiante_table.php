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
        Schema::create('estudiante', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('tipo_documento', 20)->nullable();
            $table->string('nro_documento', 15)->nullable()->unique();
            $table->string('apellido_paterno', 45)->nullable();
            $table->string('apellido_materno', 45)->nullable();
            $table->string('nombre', 45)->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('pais_nacimiento', 60)->nullable();
            $table->string('departamento_nacimiento', 60)->nullable();
            $table->string('provincia_nacimiento', 60)->nullable();
            $table->string('distrito_nacimiento', 60)->nullable();
            $table->string('lugar_nacimiento', 100)->nullable();
            $table->string('direccion_residencia', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('estado_civil', 30)->nullable();
            $table->string('grado_instruccion', 50)->nullable();
            $table->string('trabaja', 10)->nullable();
            $table->string('puesto_trabajo', 100)->nullable();
            $table->string('carga_familiar', 10)->nullable();
            $table->string('correo_electronico', 60)->nullable();
            $table->string('celular_personal', 9)->nullable();
            $table->string('internet_casa', 10)->nullable();
            $table->string('tipo_operador', 20)->nullable();
            $table->string('equipo_clases', 100)->nullable();
            $table->string('discapacidad', 100)->nullable();
            $table->string('celular_referencia', 9)->nullable();
            $table->string('parentesco_referencia', 30)->nullable();
            $table->string('lengua_originaria', 50)->nullable();

            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante');
    }
};
