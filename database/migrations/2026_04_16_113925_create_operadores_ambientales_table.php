<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operadores_ambientales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 75);
            $table->string('empresa', 150)->nullable();
            $table->string('correo', 50)->unique()->nullable();
            $table->string('celular', 10)->nullable();
            $table->string('telefono_fijo', 15)->nullable();
            $table->string('taxid', 15)->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operadores_ambientales');
    }
};
