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
        Schema::create('matricula_historial', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_matricula');
            $table->tinyInteger('estado_anterior')->nullable();
            $table->tinyInteger('estado_nuevo');
            $table->string('motivo')->nullable();

            $table->unsignedBigInteger('id_usuario')->nullable(); 
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->foreign('id_matricula')->references('id')->on('matricula')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricula_historial');
    }
};
