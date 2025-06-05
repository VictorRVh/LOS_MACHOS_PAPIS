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
        Schema::create('especialidad_madre', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->string('nombre_especialidad');
            $table->uuid('id_ciclo'); 

            $table->foreign('id_ciclo')->references('id')->on('ciclo_academico')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidad_madre');
    }
};
