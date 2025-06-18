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
        Schema::create('matricula', function (Blueprint $table) {
             $table->uuid('id')->primary();

            $table->uuid('id_grupo');
            $table->char('turno', 1)->nullable();
            $table->uuid('id_estudiante');
            $table->uuid('id_pago')->nullable();
            $table->boolean('reserva')->default(false);

            $table->foreign('id_grupo')->references('id')->on('grupo')->onDelete('cascade');
            $table->foreign('id_estudiante')->references('id')->on('estudiante')->onDelete('cascade');
            $table->foreign('id_pago')->references('id')->on('pagos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matricula');
    }
};
