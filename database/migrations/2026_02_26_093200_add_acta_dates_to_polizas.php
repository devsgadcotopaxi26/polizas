<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->date('fecha_inicio_acta_provisional')->nullable()->after('estado');
            $table->date('fecha_fin_acta_provisional')->nullable()->after('fecha_inicio_acta_provisional');
            $table->date('fecha_inicio_acta_definitiva')->nullable()->after('fecha_fin_acta_provisional');
            $table->date('fecha_fin_acta_definitiva')->nullable()->after('fecha_inicio_acta_definitiva');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_inicio_acta_provisional',
                'fecha_fin_acta_provisional',
                'fecha_inicio_acta_definitiva',
                'fecha_fin_acta_definitiva',
            ]);
        });
    }
};
