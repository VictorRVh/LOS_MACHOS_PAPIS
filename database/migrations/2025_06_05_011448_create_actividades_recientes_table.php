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
        Schema::create('actividades_recientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_role');
            $table->unsignedBigInteger('id_usuario');
            $table->string('descripcion');
            $table->date('fecha');

            $table->foreign('id_usuario')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('id_role')
                ->references('id')->on('roles')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades_recientes');
    }
};
