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
        Schema::create('grupo', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('id_programa');
            $table->uuid('id_especialidad');
            $table->uuid('id_modulo');
            $table->uuid('id_periodo');
            $table->uuid('id_convenio')->nullable();
            $table->uuid('id_docente')->nullable();

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->date('fecha_entrega_acta')->nullable();
            $table->char('seccion', 1)->nullable();
            $table->char('turno', 1)->nullable();
            $table->tinyInteger('status')->default(0);

            $table->foreign('id_programa')->references('id')->on('programa_estudio')->onDelete('cascade');
            $table->foreign('id_especialidad')->references('id')->on('especialidad_programa')->onDelete('cascade');
            $table->foreign('id_modulo')->references('id')->on('modulos')->onDelete('cascade');
            $table->foreign('id_periodo')->references('id')->on('periodo')->onDelete('cascade');
            $table->foreign('id_convenio')->references('id')->on('convenios')->onDelete('set null');
            $table->foreign('id_docente')->references('id')->on('docente')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo');
    }
};
