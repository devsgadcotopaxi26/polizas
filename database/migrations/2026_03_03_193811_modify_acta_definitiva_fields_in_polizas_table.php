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
        Schema::table('polizas', function (Blueprint $table) {
            $table->renameColumn('fecha_inicio_acta_definitiva', 'fecha_acta_definitiva');
            $table->dropColumn('fecha_fin_acta_definitiva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->renameColumn('fecha_acta_definitiva', 'fecha_inicio_acta_definitiva');
            $table->date('fecha_fin_acta_definitiva')->nullable()->after('fecha_inicio_acta_definitiva');
        });
    }
};
