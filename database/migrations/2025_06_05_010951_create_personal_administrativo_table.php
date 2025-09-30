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
        Schema::create('personal_administrativo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_usuario');
            $table->string('turno')->nullable();
            $table->string('local')->nullable();

            $table->foreign('id_usuario')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_administrativo');
    }
};
