<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Agregar columna sucursal_id
        Schema::table('polizas', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('aseguradora_id')
                ->constrained('sucursales_aseguradora')->onDelete('restrict');
        });

        // 2. Llenar la columna nueva. 
        // Para cada aseguradora, vamos a tomar la PRIMERA sucursal de esa empresa 
        // y asignar las pólizas de esa empresa a dicha sucursal.
        $polizas = DB::table('polizas')->get();

        foreach ($polizas as $poliza) {
            $primeraSucursal = DB::table('sucursales_aseguradora')
                ->where('aseguradora_id', $poliza->aseguradora_id)
                ->first();

            if ($primeraSucursal) {
                DB::table('polizas')
                    ->where('id', $poliza->id)
                    ->update(['sucursal_id' => $primeraSucursal->id]);
            }
        }

        // 3. Hacer que la columna no sea nulable (al menos para las nuevas) y eliminar aseguradora_id
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['aseguradora_id']);
            $table->dropColumn('aseguradora_id');
        });

        // Ajustar nulable a false si queremos que sea obligatoria
        DB::statement('ALTER TABLE polizas MODIFY sucursal_id BIGINT UNSIGNED NOT NULL;');
    }

    public function down(): void
    {
        Schema::table('polizas', function (Blueprint $table) {
            $table->foreignId('aseguradora_id')->nullable()->after('sucursal_id')
                ->constrained('aseguradoras')->onDelete('restrict');
        });

        $polizas = DB::table('polizas')->get();

        foreach ($polizas as $poliza) {
            if ($poliza->sucursal_id) {
                $sucursal = DB::table('sucursales_aseguradora')
                    ->where('id', $poliza->sucursal_id)
                    ->first();

                if ($sucursal) {
                    DB::table('polizas')
                        ->where('id', $poliza->id)
                        ->update(['aseguradora_id' => $sucursal->aseguradora_id]);
                }
            }
        }

        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn('sucursal_id');
        });
        
        DB::statement('ALTER TABLE polizas MODIFY aseguradora_id BIGINT UNSIGNED NOT NULL;');
    }
};
