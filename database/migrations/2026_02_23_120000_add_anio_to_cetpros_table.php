<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cetpros', function (Blueprint $table) {
            $table->string('anio')->nullable()->after('cetpro');
        });

        DB::table('cetpros')
            ->whereNull('anio')
            ->update(['anio' => 'Año de la Esperanza y el Fortalecimiento de la Democracia']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetpros', function (Blueprint $table) {
            $table->dropColumn('anio');
        });
    }
};
