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
        Schema::create('carpetas_practicas_drive', function (Blueprint $table) {
           $table->uuid('id')->primary();

            $table->uuid('id_nota_experiencia');
            $table->uuid('id_estudiante');

            $table->string('drive_file_id', 255);

            // $table->tinyInteger('status')->default(1);

            $table->timestamps();

            $table->foreign('id_nota_experiencia')
                ->references('id')
                ->on('nota_experiencia_formativa')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('id_estudiante')
                ->references('id')
                ->on('estudiante')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpetas_practicas_drive');
    }
};
