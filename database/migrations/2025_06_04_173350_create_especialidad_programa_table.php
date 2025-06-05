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
        Schema::create('especialidad_programa', function (Blueprint $table) {
            $table->uuid('id')->primary(); 
            $table->uuid('id_especialidad'); 
            $table->uuid('id_programa');     

            $table->foreign('id_especialidad')
                  ->references('id')
                  ->on('especialidad_madre')
                  ->onDelete('cascade');

            $table->foreign('id_programa')
                  ->references('id')
                  ->on('programa_estudio')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidad_programa');
    }
};
