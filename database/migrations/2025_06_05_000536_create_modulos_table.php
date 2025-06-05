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
        Schema::create('modulos', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->string('numero_modulo');
            $table->string('descripcion')->nullable();
            $table->integer('creditos')->nullable();
            $table->integer('horas')->nullable();
            $table->uuid('id_especialidad'); 

            $table->foreign('id_especialidad')
                  ->references('id')
                  ->on('especialidad_programa')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
