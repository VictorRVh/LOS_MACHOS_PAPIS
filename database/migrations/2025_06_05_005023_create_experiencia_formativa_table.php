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
        Schema::create('experiencia_formativa', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre_experiencia');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('horas');
            $table->uuid('id_grupo');
            $table->tinyInteger('status')->default(0);

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
        Schema::dropIfExists('experiencia_formativa');
    }
};
