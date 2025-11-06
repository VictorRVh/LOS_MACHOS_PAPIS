<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendario_admin', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('id_sesion');

            $table->date('fecha');
            $table->tinyInteger('laborable')->default(0);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_sesion')
                ->references('id')->on('sesiones')
                ->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendario_admin');
    }

};
