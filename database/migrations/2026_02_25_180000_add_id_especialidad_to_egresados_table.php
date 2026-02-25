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
        if (!Schema::hasColumn('egresados', 'id_especialidad')) {
            Schema::table('egresados', function (Blueprint $table) {
                $table->uuid('id_especialidad')->nullable()->after('id_grupo');
                $table->index('id_especialidad');
            });
        }

        DB::statement("
            UPDATE egresados e
            INNER JOIN grupo g ON g.id = e.id_grupo
            SET e.id_especialidad = g.id_especialidad
            WHERE e.id_especialidad IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('egresados', 'id_especialidad')) {
            Schema::table('egresados', function (Blueprint $table) {
                $table->dropIndex(['id_especialidad']);
                $table->dropColumn('id_especialidad');
            });
        }
    }
};

