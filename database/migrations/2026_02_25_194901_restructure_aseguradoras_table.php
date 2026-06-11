<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('aseguradoras', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn([
                'nombre',
                'ruc',
                'contacto',
                'telefono',
                'email',
                'direccion',
            ]);
        });

        Schema::table('aseguradoras', function (Blueprint $table) {
            // Add new columns
            $table->string('nombre_empresa', 150)->after('id');
            $table->foreignId('ciudad_id')->nullable()->after('nombre_empresa')
                ->constrained('ciudades')->onDelete('set null');

            // Correos (hasta 6)
            $table->string('correo1', 50)->nullable()->after('ciudad_id');
            $table->string('correo2', 50)->nullable()->after('correo1');
            $table->string('correo3', 50)->nullable()->after('correo2');
            $table->string('correo4', 50)->nullable()->after('correo3');
            $table->string('correo5', 50)->nullable()->after('correo4');
            $table->string('correo6', 50)->nullable()->after('correo5');

            // Celulares (hasta 3)
            $table->string('celular1', 10)->nullable()->after('correo6');
            $table->string('celular2', 10)->nullable()->after('celular1');
            $table->string('celular3', 10)->nullable()->after('celular2');

            // Teléfonos fijos (hasta 2)
            $table->string('telefono_fijo1', 10)->nullable()->after('celular3');
            $table->string('telefono_fijo2', 10)->nullable()->after('telefono_fijo1');

            // Extensiones y contacto
            $table->string('extensiones', 50)->nullable()->after('telefono_fijo2');
            $table->string('nombre_contacto', 50)->nullable()->after('extensiones');
        });
    }

    public function down(): void
    {
        Schema::table('aseguradoras', function (Blueprint $table) {
            $table->dropForeign(['ciudad_id']);
            $table->dropColumn([
                'nombre_empresa',
                'ciudad_id',
                'correo1',
                'correo2',
                'correo3',
                'correo4',
                'correo5',
                'correo6',
                'celular1',
                'celular2',
                'celular3',
                'telefono_fijo1',
                'telefono_fijo2',
                'extensiones',
                'nombre_contacto',
            ]);
        });

        Schema::table('aseguradoras', function (Blueprint $table) {
            $table->string('nombre')->after('id');
            $table->string('ruc')->unique()->nullable()->after('nombre');
            $table->string('contacto')->nullable()->after('ruc');
            $table->string('telefono')->nullable()->after('contacto');
            $table->string('email')->nullable()->after('telefono');
            $table->text('direccion')->nullable()->after('email');
        });
    }
};
