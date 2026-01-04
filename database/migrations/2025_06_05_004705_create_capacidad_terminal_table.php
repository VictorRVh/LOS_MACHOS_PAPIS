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
        Schema::create('capacidad_terminal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numero_capacidad');
            $table->string('nombre_capacidad');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
<<<<<<< HEAD
=======
            $table->string('creditos_teoricos');
            $table->string('creditos_practicos');
            $table->string('horas');
>>>>>>> fa50eb356fdfb5288d30bb9d425aa168d8386a36
            $table->dateTime('fecha_aplazada')->nullable();
            $table->uuid('id_grupo');
            $table->tinyInteger('status')->default(0);

            $table->foreign('id_grupo')
                ->references('id')->on('grupo')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacidad_terminal');
    }
};
