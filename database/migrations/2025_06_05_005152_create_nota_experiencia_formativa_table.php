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
        Schema::create('nota_experiencia_formativa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_experiencia');
            $table->string('lugar');
            $table->string('documento');
            $table->uuid('id_estudiante');
            $table->uuid('id_grupo');
            $table->boolean('status')->default(true);

            $table->foreign('id_experiencia')
                  ->references('id')->on('experiencia_formativa')
                  ->onDelete('cascade');

            $table->foreign('id_estudiante')
                  ->references('id')->on('estudiante')
                  ->onDelete('cascade');

            $table->foreign('id_grupo')
                  ->references('id')->on('grupo')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nota_experiencia_formativa');
    }
};
