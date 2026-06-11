<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Clear existing polizas data (dev environment)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('poliza_renovaciones')->truncate();
        DB::table('poliza_historial')->truncate();
        DB::table('polizas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('polizas', function (Blueprint $table) {
            // Drop beneficiario fields
            $table->dropColumn([
                'beneficiario_nombre',
                'beneficiario_email',
                'beneficiario_telefono',
                'beneficiario_cedula',
            ]);
        });

        Schema::table('polizas', function (Blueprint $table) {
            // Add new fields
            $table->foreignId('contrato_id')->nullable()->after('aseguradora_id')
                ->constrained('contratos')->onDelete('set null');
            $table->string('acta_provisional', 100)->nullable()->after('observaciones');
            $table->string('acta_definitiva', 100)->nullable()->after('acta_provisional');
            $table->text('oficios_renovacion')->nullable()->after('acta_definitiva');
        });
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['contrato_id']);
            $table->dropColumn([
                'contrato_id',
                'acta_provisional',
                'acta_definitiva',
                'oficios_renovacion',
            ]);
        });

        Schema::table('polizas', function (Blueprint $table) {
            $table->string('beneficiario_nombre')->after('fecha_vencimiento');
            $table->string('beneficiario_email')->after('beneficiario_nombre');
            $table->string('beneficiario_telefono')->nullable()->after('beneficiario_email');
            $table->string('beneficiario_cedula')->nullable()->after('beneficiario_telefono');
        });
    }
};
