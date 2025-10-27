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

<<<<<<< HEAD
            $table->string('tipo_entrega', 255);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
=======
            $table->tinyInteger('tipo_entrega');
            $table->string('nombre_entrega', 255);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();

>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
            $table->tinyInteger('status')->default(0);

            $table->uuid('id_periodo');
            $table->foreign('id_periodo')->references('id')->on('periodo')->onDelete('cascade');

            $table->boolean('mostrar')->default(0);

<<<<<<< HEAD
=======
            $table->boolean('sub_grupos')->default(0);

>>>>>>> f2878b34cbce9301735378d1394f4c2bf1f1243e
            $table->boolean('is_deleted')->default(0);

            $table->timestamps();
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
