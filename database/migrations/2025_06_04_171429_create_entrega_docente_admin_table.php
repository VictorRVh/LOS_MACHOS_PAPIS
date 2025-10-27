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
        Schema::create('entrega_docente_admin', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->tinyInteger('tipo_entrega');
            $table->string('nombre_entrega', 255);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();

            $table->tinyInteger('status')->default(0);

            $table->uuid('id_periodo');
            $table->foreign('id_periodo')->references('id')->on('periodo')->onDelete('cascade');

            $table->boolean('mostrar')->default(0);

            $table->boolean('sub_grupos')->default(0);

            $table->boolean('is_deleted')->default(0);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega_docente_admin');
    }
};
