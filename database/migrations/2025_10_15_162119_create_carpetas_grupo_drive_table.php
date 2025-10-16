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
        Schema::create('carpetas_grupo_drive', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('id_grupo');

            $table->string('drive_folder_id', 255); // ID de la carpeta en Google Drive
            $table->string('nombre_carpeta', 255)->nullable(); 

            // $table->tinyInteger('status')->default(1);

            $table->timestamps();

            $table->foreign('id_grupo')
                ->references('id')
                ->on('grupo')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpetas_grupo_drive');
    }
};
