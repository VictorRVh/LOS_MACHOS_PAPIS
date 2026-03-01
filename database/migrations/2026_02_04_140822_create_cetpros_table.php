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
        Schema::create('cetpros', function (Blueprint $table) {
            $table->id();

            $table->string('cetpro');
            
            $table->string('director');
              $table->string('anio');
            $table->string('rd_autorizacion');
            $table->string('rd_conversion')->nullable();

            $table->string('ugel');
            $table->string('dre');
            $table->string('tipo_gestion');
          
            $table->string('region');
            $table->string('provincia');
            $table->string('distrito');

            $table->string('lugar')->nullable();
            $table->string('direccion')->nullable();
            $table->string('numero')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cetpros');
    }
};
