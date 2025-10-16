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
        Schema::create('carpetas_periodo_drive', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('id_periodo');

            $table->string('drive_folder_id', 255); // ID de la carpeta en Google Drive
            $table->string('nombre_carpeta', 255)->nullable();

            $table->timestamps();

            $table->foreign('id_periodo')
                ->references('id')
                ->on('periodo')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpetas_periodo_drive');
    }
};
