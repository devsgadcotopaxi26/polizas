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
            // Eliminar columna antigua
            $table->dropColumn('tipo_seguro');

            // Agregar nuevas columnas de clasificación
            $table->enum('categoria_poliza', ['ambiental', 'obras', 'proveedores'])
                ->after('numero_poliza');

            $table->enum('subtipo_poliza', ['fiel_cumplimiento_ambiental', 'fiel_cumplimiento', 'buen_uso'])
                ->after('categoria_poliza');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropColumn(['categoria_poliza', 'subtipo_poliza']);
            $table->string('tipo_seguro')->after('numero_poliza');
        });
    }
};
