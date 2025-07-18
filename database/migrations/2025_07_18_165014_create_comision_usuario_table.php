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
        Schema::create('comision_usuario', function (Blueprint $table) {
            // UUID para comision_id
            $table->id(); // id autoincremental de la fila
            $table->uuid('id_comision');
            $table->unsignedBigInteger('id_usuario');

            // Relaciones
            $table->foreign('id_comision')->references('id')->on('comisiones')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps(); // created_at y updated_at

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comision_usuario');
    }
};
