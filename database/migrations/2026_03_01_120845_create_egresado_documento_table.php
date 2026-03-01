<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('egresado_documento', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_egresado');
            $table->tinyInteger('tipo_documento');
            $table->dateTime('fecha_emision')->nullable();
            $table->unsignedBigInteger('id_autor');

            $table->string('codigo_institucion', 50)->nullable();
            $table->string('codigo_ugel', 50)->nullable();

            $table->string('codigo')->nullable();
            $table->tinyInteger('duplicado')->default(0);

            $table->foreign('id_egresado')
                ->references('id')
                ->on('egresados')
                ->onDelete('cascade');

            $table->foreign('id_autor')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('egresado_documento');
    }
};