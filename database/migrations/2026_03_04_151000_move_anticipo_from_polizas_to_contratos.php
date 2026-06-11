<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar anticipo a contratos
        Schema::table('contratos', function (Blueprint $table) {
            $table->decimal('anticipo', 15, 2)->nullable()->after('valor_contrato');
        });

        // Quitar anticipo de polizas
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn('anticipo');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->decimal('anticipo', 15, 2)->nullable()->after('valor_asegurado');
        });

        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn('anticipo');
        });
    }
};
