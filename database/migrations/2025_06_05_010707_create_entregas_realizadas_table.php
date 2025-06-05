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
        Schema::create('entregas_realizadas', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->uuid('id_entrega');
            $table->uuid('id_docente');
            $table->string('archivo');
            $table->timestamp('fecha_entrega');
            $table->string('observacion')->nullable();

            $table->foreign('id_entrega')
                  ->references('id')->on('entrega_docente')
                  ->onDelete('cascade');

            $table->foreign('id_docente')
                  ->references('id')->on('docente')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_realizadas');
    }
};
