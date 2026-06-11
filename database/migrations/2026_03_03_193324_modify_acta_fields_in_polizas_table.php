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
            $table->renameColumn('fecha_inicio_acta_provisional', 'fecha_acta_provisional');
            $table->dropColumn('fecha_fin_acta_provisional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->renameColumn('fecha_acta_provisional', 'fecha_inicio_acta_provisional');
            $table->date('fecha_fin_acta_provisional')->nullable()->after('fecha_inicio_acta_provisional');
        });
    }
};
