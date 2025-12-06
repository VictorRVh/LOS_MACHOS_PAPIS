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
        Schema::table('capacidad_terminal', function (Blueprint $table) {
            $table->tinyInteger('status_nota')
                ->default(0)
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('capacidad_terminal', function (Blueprint $table) {
            $table->dropColumn('status_nota');
        });
    }
};
