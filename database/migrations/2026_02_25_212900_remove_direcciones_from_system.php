<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Remove direccion_id FK from polizas
        Schema::table('polizas', function (Blueprint $table) {
            $table->dropForeign(['direccion_id']);
            $table->dropColumn('direccion_id');
        });

        // Remove direccion_id FK from contratos
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropForeign(['direccion_id']);
            $table->dropColumn('direccion_id');
        });

        // Remove direccion_id from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['direccion_id']);
            $table->dropColumn('direccion_id');
        });

        // Drop direcciones table
        Schema::dropIfExists('direcciones');
    }

    public function down(): void
    {
        // Recreate direcciones table
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Re-add direccion_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones');
        });

        // Re-add direccion_id to contratos
        Schema::table('contratos', function (Blueprint $table) {
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones');
        });

        // Re-add direccion_id to polizas
        Schema::table('polizas', function (Blueprint $table) {
            $table->foreignId('direccion_id')->constrained('direcciones');
        });
    }
};
