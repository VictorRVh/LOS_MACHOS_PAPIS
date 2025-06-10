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
        Schema::create('programa_estudio', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->uuid('id_ciclo');
            $table->string('año');
            $table->string('numero_rd')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('descripcion')->nullable();

            $table->foreign('id_ciclo')
                  ->references('id')
                  ->on('ciclo_academico')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programa_estudio');
    }
};
