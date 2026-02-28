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
        Schema::table('cetpros', function (Blueprint $table) {
            if (!Schema::hasColumn('cetpros', 'director')) {
                $table->string('director')->nullable()->after('cetpro');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cetpros', function (Blueprint $table) {
            if (Schema::hasColumn('cetpros', 'director')) {
                $table->dropColumn('director');
            }
        });
    }
};

