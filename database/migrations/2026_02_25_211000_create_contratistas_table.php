<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contratistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cont', 75);
            $table->string('correo_cont', 50)->unique()->nullable();
            $table->string('celular_cont', 10)->nullable();
            $table->string('num_cedula', 10)->unique()->nullable();
            $table->string('taxid', 15)->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratistas');
    }
};
