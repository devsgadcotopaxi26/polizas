<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1. Crear la nueva tabla de sucursales
        Schema::create('sucursales_aseguradora', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aseguradora_id')
                ->constrained('aseguradoras')
                ->onDelete('cascade');
            $table->foreignId('ciudad_id')
                ->nullable()
                ->constrained('ciudades')
                ->onDelete('set null');

            $table->string('nombre_contacto', 100)->nullable();

            // Correos (hasta 6)
            $table->string('correo1', 100)->nullable();
            $table->string('correo2', 100)->nullable();
            $table->string('correo3', 100)->nullable();
            $table->string('correo4', 100)->nullable();
            $table->string('correo5', 100)->nullable();
            $table->string('correo6', 100)->nullable();

            // Celulares (hasta 3)
            $table->string('celular1', 15)->nullable();
            $table->string('celular2', 15)->nullable();
            $table->string('celular3', 15)->nullable();

            // Teléfonos fijos (hasta 2) + extensiones
            $table->string('telefono_fijo1', 15)->nullable();
            $table->string('telefono_fijo2', 15)->nullable();
            $table->string('extensiones', 50)->nullable();

            $table->timestamps();
        });

        // 2. Migrar datos existentes de aseguradoras → sucursales
        // Cada registro actual de aseguradoras se convierte en 1 sucursal
        $aseguradoras = DB::table('aseguradoras')->get();
        foreach ($aseguradoras as $a) {
            // Solo migrar si tiene al menos algún dato de contacto
            DB::table('sucursales_aseguradora')->insert([
                'aseguradora_id'  => $a->id,
                'ciudad_id'       => $a->ciudad_id ?? null,
                'nombre_contacto' => $a->nombre_contacto ?? null,
                'correo1'         => $a->correo1 ?? null,
                'correo2'         => $a->correo2 ?? null,
                'correo3'         => $a->correo3 ?? null,
                'correo4'         => $a->correo4 ?? null,
                'correo5'         => $a->correo5 ?? null,
                'correo6'         => $a->correo6 ?? null,
                'celular1'        => $a->celular1 ?? null,
                'celular2'        => $a->celular2 ?? null,
                'celular3'        => $a->celular3 ?? null,
                'telefono_fijo1'  => $a->telefono_fijo1 ?? null,
                'telefono_fijo2'  => $a->telefono_fijo2 ?? null,
                'extensiones'     => $a->extensiones ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // 3. Eliminar columnas redundantes de aseguradoras
        Schema::table('aseguradoras', function (Blueprint $table) {
            $table->dropForeign(['ciudad_id']);
            $table->dropColumn([
                'ciudad_id',
                'nombre_contacto',
                'correo1', 'correo2', 'correo3', 'correo4', 'correo5', 'correo6',
                'celular1', 'celular2', 'celular3',
                'telefono_fijo1', 'telefono_fijo2',
                'extensiones',
            ]);
        });
    }

    public function down(): void
    {
        // Restaurar columnas en aseguradoras
        Schema::table('aseguradoras', function (Blueprint $table) {
            $table->foreignId('ciudad_id')->nullable()->constrained('ciudades')->onDelete('set null');
            $table->string('nombre_contacto', 50)->nullable();
            $table->string('correo1', 50)->nullable();
            $table->string('correo2', 50)->nullable();
            $table->string('correo3', 50)->nullable();
            $table->string('correo4', 50)->nullable();
            $table->string('correo5', 50)->nullable();
            $table->string('correo6', 50)->nullable();
            $table->string('celular1', 10)->nullable();
            $table->string('celular2', 10)->nullable();
            $table->string('celular3', 10)->nullable();
            $table->string('telefono_fijo1', 10)->nullable();
            $table->string('telefono_fijo2', 10)->nullable();
            $table->string('extensiones', 50)->nullable();
        });

        Schema::dropIfExists('sucursales_aseguradora');
    }
};
