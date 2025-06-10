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
        Schema::create('sesiones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre_sesion');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('descripcion')->nullable();
            $table->string('archivo_sesion')->nullable();

            $table->uuid('id_calendario');
            $table->uuid('id_capacidad');
            $table->uuid('id_entrega')->nullable();

            $table->tinyInteger('status')->default(0);

            $table->foreign('id_calendario')
                  ->references('id')->on('calendario_admin')
                  ->onDelete('cascade');

            $table->foreign('id_capacidad')
                  ->references('id')->on('capacidad_terminal')
                  ->onDelete('cascade');

            $table->foreign('id_entrega')
                  ->references('id')->on('entrega_docente')
                  ->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
