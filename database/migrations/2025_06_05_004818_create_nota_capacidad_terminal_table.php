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
        Schema::create('nota_capacidad_terminal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nota_capacidad');
            $table->uuid('id_grupo');
            $table->uuid('id_capacidad');
            $table->uuid('id_estudiante');

            $table->foreign('id_grupo')
                  ->references('id')->on('grupo')
                  ->onDelete('cascade');

            $table->foreign('id_capacidad')
                  ->references('id')->on('capacidad_terminal')
                  ->onDelete('cascade');

            $table->foreign('id_estudiante')
                  ->references('id')->on('estudiante')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_capacidad_terminal');
    }
};
