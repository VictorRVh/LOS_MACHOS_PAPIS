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
        Schema::create('estudiante_documento', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_matricula');
            $table->tinyInteger('tipo_documento');
            $table->dateTime('fecha_emision')->nullable();

            $table->unsignedBigInteger('id_autor');

            $table->string('codigo')->nullable();
            $table->tinyInteger('duplicado')->default(0);

            $table->foreign('id_matricula')->references('id')->on('matricula')->onDelete('cascade');
            $table->foreign('id_autor')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_documento');
    }
};
