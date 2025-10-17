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
        Schema::create('carpetas_entrega_drive', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('id_entrega_admin');
            $table->foreign('id_entrega_admin')
                ->references('id')
                ->on('entrega_docente_admin')
                ->onDelete('cascade');

            // Grupo al que pertenece la carpeta
            $table->uuid('id_grupo');
            $table->foreign('id_grupo')
                ->references('id')
                ->on('grupo')
                ->onDelete('cascade');

            // ID de carpeta en Google Drive
            $table->string('drive_folder_id');

            $table->string('nombre_carpeta');

            // $table->tinyInteger('is_deleted')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpetas_entrega_drive');
    }
};
