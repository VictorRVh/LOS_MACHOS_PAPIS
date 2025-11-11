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

            $table->uuid('id_experiencia');

            $table->string('drive_folder_id', 255);

            $table->timestamps();

            $table->foreign('id_experiencia')
                ->references('id')
                ->on('experiencia_formativa')
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
